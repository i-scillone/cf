<?php
require_once './vendor/autoload.php';
require_once './codice_fiscale.php';

$dbg=new MyClasses\Debug();
$smarty=new Smarty\Smarty();
$smarty->setTemplateDir('./templates');
$dbg->log($_REQUEST);
$smarty->assign('serverURL',MyClasses\Net::pathOnServer());
//$smarty->assign('noSi',['no','sì']);
if (!file_exists('config.ini')) {
    $smarty->assign('error','FILE DI CONFIGURAZIONE MANCANTE!');
    $smarty->display('main.html');
} elseif (isset($_REQUEST['goTo'])) {
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
            $smarty->assign('feedback',"Il codice fiscale è: <i>{$cf}</i>");
            $smarty->display('main.html');
            break;
        case 'validate':
            $ok=CodiceFiscale::valida($_REQUEST['cf']);
            if ($ok) {
                try {
                    $db=new MyClasses\DB('sqlite:codici.sqlite');
                    $sel=$db->prepare('SELECT nome,provincia FROM main WHERE codice=?');
                    $dati=CodiceFiscale::estraiDati($_REQUEST['cf']);
                    $feedback="<i>{$_REQUEST['cf']}</i> è un codice valido<br>";
                    $feedback.='ed appartiene ad ';
                    $feedback.=($dati['sesso']=='M'? 'un maschio': 'una femmina').' ';
                    $f=new IntlDateFormatter('it',IntlDateFormatter::MEDIUM,IntlDateFormatter::NONE);
                    $ddn=new DateTimeImmutable($dati['data_nascita']);
                    $feedback.='nat'.($dati['sesso']=='M'?'o':'a').' il '.strtoupper($f->format($ddn));
                    $sel->execute([$dati['codice_comune']]);
                    $ldn=$sel->fetch(PDO::FETCH_OBJ);
                    $feedback.=" a/in {$ldn->nome} ({$ldn->provincia})";
                    $smarty->assign('feedback',$feedback);
                } catch (Exception $e) {
                    $smarty->assign(
                        'error',
                        "Errore alla riga {$e->getLine()}: «{$e->getMessage()}"
                    );
                }
            } else {
                $smarty->assign('error',$_REQUEST['cf'].' non è un codice valido!');
            }
            $smarty->display('validate.html');
            break;
        case 'tin':
            $conf=parse_ini_file('config.ini');
            $curl=curl_init('https://ec.europa.eu/taxation_customs/tin/rest-api/tinRequest');
            curl_setopt_array($curl,[
                CURLOPT_RETURNTRANSFER=>true,
                CURLOPT_POST=>true,
                CURLOPT_HTTPHEADER=>['Content-Type: application/json'],
                CURLOPT_POSTFIELDS=>json_encode([
                    'msCode'=>$_REQUEST['msCode'],
                    'tinNumber'=>$_REQUEST['tinNumber']
                ]),
                CURLOPT_PROXY=>$conf['url'] ?? null,
                CURLOPT_PROXYUSERPWD=>$conf['userpass'] ?? null
            ]);
            $buf=curl_exec($curl);
            $r=json_decode($buf);
            $feedback="ESITO DEL CONTROLLO SU: <i>{$_REQUEST['tinNumber']}</i>…<br>";
            $feedback.=sprintf(
                'STRUTTURA: %s<br>',
                $r->result->structureValid?'OK':'ERR'
            );
            $feedback.=sprintf(
                'SINTASSI: %s',$r->result->syntaxValid?'OK':'ERR'
            );
            $smarty->assign('feedback',$feedback);
            $smarty->display('ue.html');
            break;
        default:
            $smarty->assign('error','NOT IMPLEMENTED!');
            $smarty->display('main.html');
    }
} else {
    $smarty->display('main.html');
}
