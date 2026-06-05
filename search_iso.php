<?php
require_once 'vendor/autoload.php';

$dbg=new MyClasses\Debug();
try {
    $db=new MyClasses\DB('sqlite:codici.sqlite');
    $sel=$db->prepare('SELECT alpha2 AS `value`, nome AS `label` FROM iso WHERE nome LIKE ?');
    $sel->execute(['%'.$_GET['term'].'%']);
    $res=$sel->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($res);
} catch (Exception $e) {
    $dbg->log("Errore alla riga {$e->getLine()}: «{$e->getMessage()}»");
}