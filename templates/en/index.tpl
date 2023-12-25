{include file='../header.tpl'}
{include file='top-menu.tpl'}
{include file='menu.tpl'}
<div class="main">
    <div class="question">
        <p>Task:</p>
        {include file="questions/{$QuestionID}.tpl"}
        <p>
        Write your answer below and press "Test it" button
        </p>
    </div>
    <div class="code-wrapper" id="sql-code" name="sql-code"></div>
    <div class="code-buttons">
        <button class="button" onClick="getHelp('{$Lang}', '{$DB}', {$QuestionID})">Get help</button>
        <button class="button" onClick="runQuery('{$Lang}', '{$DB}', {$QuestionID})">Run query</button>
        <button class="button" onClick="testQuery('{$Lang}', '{$DB}', {$QuestionID})">Test it!</button>
    </div>
    <div class="code-result" id="code-result"></div>
</div>

<div class="right">
    {include file='sakila.tpl'}
</div>
{include file='../footer.tpl'}