<?php
require_once 'vendor/autoload.php';

$db=new MyClasses\DB('sqlite:codici.sqlite');
try {
    $sel=$db->prepare('SELECT * FROM main WHERE name LIKE ?');
    $sel->execute(['%'.$_GET['term'].'%']);
    $res=$sel->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $db->query('CREATE TABLE codici (id INTEGER PRIMARY KEY, codice TEXT, descrizione TEXT)');
}