<?php
require_once './vendor/autoload.php';
$dbg=new MyClasses\Debug();
$smarty=new Smarty\Smarty();
$smarty->setTemplateDir('./templates');
const INIT_DB=<<<SQL
CREATE TABLE IF NOT EXISTS comuni (
    codice TEXT PRIMARY KEY,
    nome TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS stati (
    codice TEXT PRIMARY KEY,
    nome TEXT NOT NULL
);
SQL;
$dbg->log($_REQUEST);
$smarty->assign(
    'serverURL',(($_SERVER['HTTPS'] ?? 'off') == 'on' ? 'https' : 'http') . 
    '://' . $_SERVER['HTTP_HOST']
);
//$smarty->assign('noSi',['no','sì']);
if (isset($_REQUEST['goTo'])) {
    $smarty->display($_REQUEST['goTo'].'.html');
} elseif (isset($_REQUEST['action'])) {
    switch ($_REQUEST['action']) {
        case 'upload':
            try {
                $db=new MyClasses\DB('sqlite:'.__DIR__.'/codici.sqlite');
                $db->exec(INIT_DB);
                $f=fopen($_REQUEST['file'],'r');
                $table=pathinfo($_REQUEST['file'],PATHINFO_FILENAME);
                $db->exec('DELETE FROM '.$table);
                $ins=$db->prepare("INSERT INTO {$table} VALUES(?,?)");
                $firstRow=true;
                while ($row=fgetcsv($f,null,';')) {
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
                }
                fclose($f);
                $db=null;
                $smarty->assign('feedback',"Tabella $table importata");
            } catch (Exception $e) {
                $smarty->assign(
                    'error',
                    "Errore alla riga {$e->getLine()}: «{$e->getMessage()}»"
                );
            }
            $smarty->display('download.html');
            break;
        default:
            $smarty->assign('error','NOT IMPLEMENTED!');
            $smarty->display('index.html');
    }
} else {
    $smarty->display('index.html');
}
