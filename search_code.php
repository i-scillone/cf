<?php
require_once 'vendor/autoload.php';
const SEARCH=<<<SQL
SELECT codice AS `value`, nome AS `label` 
FROM main 
WHERE nome LIKE ?
ORDER BY
    CASE 
        WHEN nome=? THEN 1
        WHEN nome LIKE ? THEN 2
        ELSE 3
    END,
    nome;
SQL;
$dbg=new MyClasses\Debug();
try {
    $db=new MyClasses\DB('sqlite:codici.sqlite');
    $sel=$db->prepare(SEARCH);
    $contains='%'.$_GET['term'].'%';
    $startsWith=$_GET['term'].'%';
    $sel->execute([$contains,$_GET['term'],$startsWith]);
    $res=$sel->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($res);
} catch (Exception $e) {
    $dbg->log("Errore alla riga {$e->getLine()}: «{$e->getMessage()}»");
}