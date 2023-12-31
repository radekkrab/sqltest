<?php
class Query 
{
    private $sql;

    private $hash;

    public function __construct(string $sql)
    {
        $this->sql = $sql;
    }

    private function setHash(string $query) : string {
        $ch = curl_init( "https://sqlize.online/hash.php" );
        # Setup request to send json via POST.
        $payload = json_encode( [
            "language" => "sql", 
            "code" => $this->sql, 
            "sql_version" => "mysql80_sakila"
        ]);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        # Return response instead of printing.
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        # Send request.
        $this->hash = curl_exec($ch);
        curl_close($ch);
    }

    public function getResult(string $db, string $format) : string {
        $this->setHash();
        $ch = curl_init( "https://sqlize.online/sqleval.php" );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "sqlses={$this->hash}&sql_version={$db}&format={$format}");
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        # Send request.
        $result = curl_exec($ch);
        curl_close($ch);
        # Print response.
        return $result;
    }
}