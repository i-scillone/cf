<?php
require_once './vendor/autoload.php';
require_once './codice_fiscale.php';

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
CREATE VIEW IF NOT EXISTS main AS
SELECT * FROM comuni
UNION ALL
SELECT * FROM stati;
SQL;
$dbg->log($_REQUEST);
$smarty->assign('serverURL',MyClasses\Net::pathOnServer());
//$smarty->assign('noSi',['no','sì']);
if (isset($_REQUEST['goTo'])) {
    $smarty->display($_REQUEST['goTo'].'.html');
} elseif (isset($_REQUEST['action'])) {
    switch ($_REQUEST['action']) {
        case 'calculate':
            $cf = CodiceFiscale::genera(
                $_REQUEST['nome'],
                $_REQUEST['cognome'],
                $_REQUEST['ddn'],
                $_REQUEST['sesso'],
                $_REQUEST['ldn']
            );
            $smarty->assign('feedback',"Il codice fiscale è: $cf");
            $smarty->display('main.html');
            break;
        case 'validate':
            $ok=CodiceFiscale::valida($_REQUEST['cf']);
            if ($ok) {
                try {
                    $db=new MyClasses\DB('sqlite:codici.sqlite');
                    $sel=$db->prepare('SELECT nome FROM main WHERE codice=?');
                    $dati=CodiceFiscale::estraiDati($_REQUEST['cf']);
                    $feedback=$_REQUEST['cf'].' è valido<br>';
                    $feedback.='ed appartiene ad ';
                    $feedback.=($dati['sesso']=='M'? 'uno uomo': 'una donna').' ';
                    $f=new IntlDateFormatter('it',IntlDateFormatter::LONG,IntlDateFormatter::NONE);
                    $ddn=new DateTimeImmutable($dati['data_nascita']);
                    $feedback.='nat'.($dati['sesso']=='M'?'o':'a').' il '.$f->format($ddn);
                    $sel->execute([$dati['codice_comune']]);
                    $nome=$sel->fetchColumn();
                    $feedback.=" a/in {$nome}";
                    $smarty->assign('feedback',$feedback);
                } catch (Exception $e) {
                    $smarty->assign(
                        'error',
                        "Errore alla riga {$e->getLine()}: «{$e->getMessage()}"
                    );
                }
            } else {
                $smarty->assign('error',$_REQUEST['cf'].' non è valido!');
            }
            $smarty->display('validate.html');
            break;
        default:
            $smarty->assign('error','NOT IMPLEMENTED!');
            $smarty->display('main.html');
    }
} else {
    $smarty->display('main.html');
}
