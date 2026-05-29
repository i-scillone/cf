<?php
require_once './vendor/autoload.php';
$dbg=new MyClasses\Debug();
$smarty=new Smarty\Smarty();
$smarty->setTemplateDir('./templates');

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
        case 'insert':
            $f=fopen($_REQUEST['file'],'r');
            $firstRow=true;
            while ($row=fgetcsv($f,null,';')) {
                if ($firstRow) {
                    $firstRow=false;
                    continue;
                }
                $dbg->log($row);
            }
            fclose($f);
            $smarty->display('index.html');
            break;
        default:
            $smarty->assign('feedback','<p class="font-red blink-fast">NOT IMPLEMENTED!</p>');
            $smarty->display('index.html');
    }
} else {
    $smarty->display('index.html');
}
