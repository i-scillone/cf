<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Taxpayer Identification Number</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body>
<?php
function toJson(): string
{
    $buf=$_POST;
    unset($buf['doIt']);
    return json_encode($buf);
}
function textInput(string $name,string $default='')
{
    printf(
        '<input id="%s" name="%s" type="text" class="form-control" value="%s">',
        $name, $name, $_POST[$name] ?? $default
    );
}
?>
<div class="container">
    <h1>Controllo del Taxpayer Identification Number</h1>
    <form method="post">
        <div class="mb-1">
            <label for="msCode" class="form-label">Stato (codice ISO)</label>
            <?php textInput('msCode','IT'); ?>
        </div>
        <div class="mb-1">
            <label for="tinNumber" class="form-label">TIN</label>
            <?php textInput('tinNumber'); ?>
        </div>
        <button name="doIt" type="submit" class="btn btn-primary">Controlla</button>
    </form>
<?php
if (isset($_POST['doIt'])) {
    $curl=curl_init('https://ec.europa.eu/taxation_customs/tin/rest-api/tinRequest');
    $data=toJson();
    curl_setopt_array($curl,[
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_POST=>true,
        CURLOPT_HTTPHEADER=>['Content-Type: application/json'],
        CURLOPT_POSTFIELDS=>$data,
        CURLOPT_PROXY=>'proxy.giustizia.it:80',
        CURLOPT_PROXYUSERPWD=>'ivan.scillone:USW8-gxo3'
    ]);
    $buf=curl_exec($curl);
    $r=json_decode($buf);
    printf('<div>Struttura: %s</div>',$r->result->structureValid?'&#x2705;':'&#x274C');
    printf('<div>Sintassi: %s</div>',$r->result->syntaxValid?'&#x2705;':'&#x274C');
}
?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>