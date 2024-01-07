<?php
class Question 
{
    /**
     * DB hahdler 
     *
     * @var PDO
     */
    private $dbh;

    /**
     * Question id
     *
     * @var integer
     */
    private $id;

    public function __construct(PDO $dbh, string $id)
    {
        $this->dbh  = $dbh;
        $this->id = (int)$id;
    }
    /**
     * Returns question DB template name
     *
     * @return string
     */
    public function getDBTemplate(): string 
    {
        $stmt = $this->dbh->prepare("SELECT db_template FROM questions WHERE id = ?");
        $stmt->execute([$this->id]);
        return (string)$stmt->fetchColumn();
    }
    /**
     * Returns question task for provided language
     *
     * @param string $lang
     * @return string
     */
    public function get(string $lang): string 
    {
        $stmt = $this->dbh->prepare("SELECT task_{$lang} FROM questions WHERE id = ?");
        $stmt->execute([$this->id]);
        return (string)$stmt->fetchColumn();
    }
    /**
     * Returns question hint for provided language
     *
     * @param string $lang
     * @return string
     */
    public function getHint(string $lang): string 
    {
        $stmt = $this->dbh->prepare("SELECT hint_{$lang} FROM questions WHERE id = ?");
        $stmt->execute([$this->id]);
        $hint = (string)$stmt->fetchColumn();

        $dafaultHint = [
            'en' => 'Try to complete this task without any hints.',
            'ru' => 'Попробуйте выполнить это задание без подсказок.'
        ];
        return $hint ?? $dafaultHint[$lang];
    }
    /**
     * Returns question db name
     *
     * @return string
     */
    public function getDB(): string 
    {
        $stmt = $this->dbh->prepare("SELECT db FROM questions WHERE id = ?");
        $stmt->execute([$this->id]);
        return (string)$stmt->fetchColumn();
    }
    /**
     * Returns previous questio Id
     *
     * @return integer
     */
    public function getPreviousId (): int 
    {
        $stmt = $this->dbh->prepare("
            SELECT nq.id
            FROM questions nq
            JOIN questions cq ON nq.category_id = cq.category_id and nq.sequence_position < cq.sequence_position
            WHERE cq.id = ?
            ORDER BY nq.sequence_position DESC
            LIMIT 1
        ");
        $stmt->execute([$this->id]);
        return (int)$stmt->fetchColumn();
    }
    /**
     * Returns next question Id
     *
     * @return integer
     */
    public function getNextId (): int 
    {
        $stmt = $this->dbh->prepare("
            SELECT nq.id
            FROM questions nq
            JOIN questions cq ON nq.category_id = cq.category_id and nq.sequence_position > cq.sequence_position
            WHERE cq.id = ?
            ORDER BY nq.sequence_position ASC
            LIMIT 1
        ");
        $stmt->execute([$this->id]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Check query using regular expressions
     *
     * @param string $query
     * @return array
     */
    public function checkQuery(string $query)
    {
        if (empty($query)) {
            $hints['emptyQuery'] = true;
            return [
                'ok' => false,
                'hints' => $hints
            ];
        }
        $stmt = $this->dbh->prepare("SELECT query_match queryMatch, query_not_match queryNotMatch FROM questions WHERE id = ?");
        $stmt->execute([$this->id]);
        $questionData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (
            (isset($questionData['queryMatch']) && !preg_match($questionData['queryMatch'], $query)) ||
            (isset($questionData['queryNotMatch']) && preg_match($questionData['queryNotMatch'], $query))
        ) {
            $hints['wrongQuery'] = true;
            return [
                'ok' => false,
                'hints' => $hints
            ];
        }
        return [
            'ok' => true
        ];
    }

    public function checkQueryResult(string $queryResult)
    {
        
        $stmt = $this->dbh->prepare("SELECT query_valid_result FROM questions WHERE id = ?");
        $stmt->execute([$this->id]);
        $questionData = $stmt->fetch(PDO::FETCH_ASSOC);
        $queryValidResult = json_decode($questionData['query_valid_result'])[0];
        try {
            $resultObject = json_decode($queryResult);
            if (!$resultObject) {
                return [
                    'ok' => false
                ];
            }
            //check columns count
            if (count($resultObject[0]->headers) !== count($queryValidResult->headers)) {
                $hints['columnsCount'] = count($queryValidResult->headers);
                return [
                    'ok' => false,
                    'hints' => $hints
                ];
            }
    
            //check columns order
            $diff = array_udiff($resultObject[0]->headers, $queryValidResult->headers,
                function ($obj_a, $obj_b) {
                    return strcmp($obj_a->header, $obj_b->header);
                }
            );
            if (count($diff) > 0) {
                $hints['columnsList'] = "`" . implode('`, `',array_column($queryValidResult->headers, 'header')) . "`";
                return [
                    'ok' => false,
                    'hints' => $hints
                ];
            }
    
            // check rows count 
            if (count($resultObject[0]->data) !== count($queryValidResult->data)) {
                $hints['rowsCount'] = count($queryValidResult->data);
                return [
                    'ok' => false,
                    'hints' => $hints
                ];
            }
    
            // check rows order
            foreach ($queryValidResult->data as $i => $row) {
                if ($row !== $resultObject[0]->data[$i]) {
                    $hints['rowsData'] = ['rowNumber' => $i + 1, 'rowData' => "'" . implode("', '", $row) . "'" ];
                    return [
                        'ok' => false,
                        'hints' => $hints
                    ];
                }
            }
            return [
                'ok' => true
            ];
        } catch(Exception $e) {
            // var_dump($e);
            return [
                'ok' => false
            ];
        }
    }
}