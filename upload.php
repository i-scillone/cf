<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UPLOAD</title>
    <style>
    body { 
        color: #ddbbff; background: black;
        font: 14px monospace;
    }
    p { margin: 0; }
    </style>
</head>
<body>
<?php
require_once 'vendor/autoload.php';
const INIT_DB=<<<SQL
CREATE TABLE IF NOT EXISTS comuni (
    codice TEXT PRIMARY KEY,
    nome TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS stati (
    codice TEXT PRIMARY KEY,
    nome TEXT NOT NULL
);
CREATE VIEW IF NOT EXISTS main AS
SELECT * FROM comuni
UNION ALL
SELECT * FROM stati;
SQL;

try {
    $db=new MyClasses\DB('sqlite:'.__DIR__.'/codici.sqlite');
    $db->exec(INIT_DB);
    $f=@fopen($_REQUEST['file'],'r');
    if (!$f) {
        throw new Exception('Impossibile aprire il file');
    }
    $table=pathinfo($_REQUEST['file'],PATHINFO_FILENAME);
    $db->exec('DELETE FROM '.$table);
    $ins=$db->prepare("INSERT INTO {$table} VALUES(?,?)");
    $firstRow=true;
    while ($row=fgetcsv($f,null,';','"','\\')) {
        set_time_limit(60);
        if ($firstRow) {
            $firstRow=false;
            continue;
        }
        if ($table=='comuni') {
            $ins->bindValue(1,$row[0],PDO::PARAM_STR);
            $ins->bindValue(2,$row[2],PDO::PARAM_STR);
        } else {
            $ins->bindValue(1,$row[0],PDO::PARAM_STR);
            $ins->bindValue(2,$row[1],PDO::PARAM_STR);
        }
        $ins->execute();
        echo '<p>'.json_encode($row,JSON_UNESCAPED_SLASHES)."</p>\n";
        flush();
    }
    fclose($f);
    $db=null;
} catch (Exception $e) {
    exit("<p style='color: #ff5555'>Errore alla riga {$e->getLine()}: «{$e->getMessage()}»</p>\n");
}
?>
</body>
</html>