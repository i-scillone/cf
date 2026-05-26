<?php
require_once './vendor/autoload.php';

$dbg=new MyClasses\Debug();
$smarty=new Smarty\Smarty();
$smarty->setTemplateDir('./templates');

$smarty->assign(
    'serverURL',(($_SERVER['HTTPS'] ?? 'off') == 'on' ? 'https' : 'http') . 
    '://' . $_SERVER['HTTP_HOST']
);
$smarty->assign('noSi',['no','sì']);
if (isset($_REQUEST['action'])) {
    switch ($_REQUEST['action']) {
        case 'download':
            $dbg->log('download');
            $smarty->display('download.html');
            break;
        case 'insert':
            $smarty->assign('feedback',json_encode($_REQUEST));
            $smarty->display('index.html');
            break;
    }
} else {
    $smarty->display('index.html');
}
