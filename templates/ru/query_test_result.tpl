{* {var_dump($QeryTestResult)} *}
{if $QeryTestResult.ok}
    Отлично! Можно приступать к следующему тесту.
{else}
    К сожалению неверно. 
    {if array_key_exists('hints', $QeryTestResult) }
        {if array_key_exists('columnsCount', $QeryTestResult.hints) }
            <p>Подсказка: результирующая таблица должна состоять из {$QeryTestResult.hints.columnsCount} колонок.</p>
        {/if}
        {if array_key_exists('columnsList', $QeryTestResult.hints) }
            <p>Подсказка: результирующая таблица должна состоять из следующих колонок: {$QeryTestResult.hints.columnsList}.</p>
        {/if}
        {if array_key_exists('rowsCount', $QeryTestResult.hints) }
            <p>Подсказка: результат должен содержать {$QeryTestResult.hints.rowsCount} строк.</p>
        {/if}
    {/if}
   Попробуйте ещё раз.
{/if}
