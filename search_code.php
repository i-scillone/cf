<?php
require_once 'vendor/autoload.php';

$dbg=new MyClasses\Debug();
try {
    $db=new MyClasses\DB('sqlite:codici.sqlite');
    $sel=$db->prepare('SELECT codice AS `value`, nome AS `label` FROM main WHERE nome LIKE ?');
    $sel->execute(['%'.$_GET['term'].'%']);
    $res=$sel->fetchAll(PDO::FETCH_ASSOC);
    $dbg->log($res);
    echo json_encode($res);
} catch (Exception $e) {
    $dbg->log("Errore alla riga {$e->getLine()}: «{$e->getMessage()}»");
}