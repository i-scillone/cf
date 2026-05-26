<?php
try { 
	$db=new PDO('sqlite:cod_fis.db'); 
} catch (PDOException $e) { 
	die($e->getMessage); 
}
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$st=$db->prepare('SELECT comune FROM comuni WHERE comune LIKE ?');
$buf=[];
$st->execute(array($_GET['term'].'%'));
$rows=$st->fetchAll(PDO::FETCH_NUM);
foreach ($rows as $comune) $buf[]=$comune[0];
$st=$db->prepare('SELECT denominaz FROM stati WHERE denominaz LIKE ?');
$st->execute(array($_GET['term'].'%'));
$rows=$st->fetchAll(PDO::FETCH_NUM);
foreach ($rows as $stato) $buf[]=$stato[0];
echo json_encode($buf);
$db=null;
?>
