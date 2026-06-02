<?php
class CodiceFiscale
{
    // ---------------------------------------------------------
    // GENERAZIONE
    // ---------------------------------------------------------
    public static function genera(
        string $nome,
        string $cognome,
        string $dataNascita,
        string $sesso,
        string $codiceComune
    ): string {
        $cf  = self::codiceCognome($cognome);
        $cf .= self::codiceNome($nome);
        $cf .= self::codiceDataSesso($dataNascita, $sesso);
        $cf .= strtoupper($codiceComune);
        $cf .= self::carattereControllo($cf);

        return $cf;
    }

    // ---------------------------------------------------------
    // VALIDAZIONE
    // ---------------------------------------------------------
    public static function valida(string $cf): bool
    {
        $cf = strtoupper($cf);

        // 1. Formato
        if (!preg_match('/^[A-Z0-9]{16}$/', $cf)) {
            return false;
        }

        // 2. Espansione omocodie sui primi 15 caratteri
        $cf15 = self::espandiOmocodia(substr($cf, 0, 15));

        // 3. Calcolo carattere di controllo
        $controlloCalcolato = self::carattereControllo($cf15);
        $controlloFornito = $cf[15];

        return $controlloCalcolato === $controlloFornito;
    }

    // ---------------------------------------------------------
    // PARTI DEL CODICE FISCALE
    // ---------------------------------------------------------
    public static function codiceCognome(string $cognome): string
    {
        return self::estraiConsonantiVocali(strtoupper($cognome));
    }

    public static function codiceNome(string $nome): string
    {
        return self::estraiConsonantiVocali(strtoupper($nome), true);
    }

    public static function codiceDataSesso(string $data, string $sesso): string
    {
        [$anno, $mese, $giorno] = explode('-', $data);

        $mesi = [
            1=>'A',2=>'B',3=>'C',4=>'D',5=>'E',6=>'H',
            7=>'L',8=>'M',9=>'P',10=>'R',11=>'S',12=>'T'
        ];

        $codice  = substr($anno, -2);
        $codice .= $mesi[(int)$mese];

        $giorno = (int)$giorno + ($sesso === 'F' ? 40 : 0);
        $codice .= str_pad($giorno, 2, '0', STR_PAD_LEFT);

        return $codice;
    }

    // ---------------------------------------------------------
    // CARATTERE DI CONTROLLO
    // ---------------------------------------------------------
    public static function carattereControllo(string $cf15): string
    {
        $pari = [
            '0'=>0,'1'=>1,'2'=>2,'3'=>3,'4'=>4,'5'=>5,'6'=>6,'7'=>7,'8'=>8,'9'=>9,
            'A'=>0,'B'=>1,'C'=>2,'D'=>3,'E'=>4,'F'=>5,'G'=>6,'H'=>7,'I'=>8,'J'=>9,
            'K'=>10,'L'=>11,'M'=>12,'N'=>13,'O'=>14,'P'=>15,'Q'=>16,'R'=>17,'S'=>18,'T'=>19,
            'U'=>20,'V'=>21,'W'=>22,'X'=>23,'Y'=>24,'Z'=>25
        ];

        $dispari = [
            '0'=>1,'1'=>0,'2'=>5,'3'=>7,'4'=>9,'5'=>13,'6'=>15,'7'=>17,'8'=>19,'9'=>21,
            'A'=>1,'B'=>0,'C'=>5,'D'=>7,'E'=>9,'F'=>13,'G'=>15,'H'=>17,'I'=>19,'J'=>21,
            'K'=>2,'L'=>4,'M'=>18,'N'=>20,'O'=>11,'P'=>3,'Q'=>6,'R'=>8,'S'=>12,'T'=>14,
            'U'=>16,'V'=>10,'W'=>22,'X'=>25,'Y'=>24,'Z'=>23
        ];

        $somma = 0;
        for ($i = 0; $i < 15; $i++) {
            $c = $cf15[$i];
            $somma += ($i % 2 === 0) ? $dispari[$c] : $pari[$c];
        }

        return chr(($somma % 26) + ord('A'));
    }

    // ---------------------------------------------------------
    // OMOCODIE
    // ---------------------------------------------------------
    private static function espandiOmocodia(string $cf15): string
    {
        $mappa = [
            'L' => '0', 'M' => '1', 'N' => '2', 'P' => '3',
            'Q' => '4', 'R' => '5', 'S' => '6', 'T' => '7',
            'U' => '8', 'V' => '9'
        ];

        $posizioni = [6, 7, 9, 10, 12, 13, 14];

        foreach ($posizioni as $pos) {
            $c = $cf15[$pos];
            if (isset($mappa[$c])) {
                $cf15[$pos] = $mappa[$c];
            }
        }

        return $cf15;
    }

    // ---------------------------------------------------------
    // UTILITY
    // ---------------------------------------------------------
    private static function estraiConsonantiVocali(string $str, bool $isNome = false): string
    {
        $consonanti = preg_replace('/[^BCDFGHJKLMNPQRSTVWXYZ]/i', '', $str);
        $vocali = preg_replace('/[^AEIOU]/i', '', $str);

        if ($isNome && strlen($consonanti) >= 4) {
            return $consonanti[0] . $consonanti[2] . $consonanti[3];
        }

        $ris = $consonanti . $vocali;
        return str_pad(substr($ris, 0, 3), 3, 'X');
    }
    public static function estraiDati(string $cf): array
    {
        $cf = strtoupper($cf);

        // Espansione omocodie sui primi 15 caratteri
        $cf15 = self::espandiOmocodia(substr($cf, 0, 15));

        // --- COGNOME ---
        $cognome = substr($cf, 0, 3);

        // --- NOME ---
        $nome = substr($cf, 3, 3);

        // --- ANNO ---
        $anno = (int) substr($cf15, 6, 2);
        $anno += ($anno >= 0 && $anno <= (int)date('y')) ? 2000 : 1900;

        // --- MESE ---
        $mesi = [
            'A'=>1,'B'=>2,'C'=>3,'D'=>4,'E'=>5,'H'=>6,
            'L'=>7,'M'=>8,'P'=>9,'R'=>10,'S'=>11,'T'=>12
        ];
        $mese = $mesi[$cf[8]];

        // --- GIORNO + SESSO ---
        $giornoCod = (int) substr($cf15, 9, 2);
        $sesso = ($giornoCod > 40) ? 'F' : 'M';
        $giorno = ($giornoCod > 40) ? $giornoCod - 40 : $giornoCod;

        // --- CODICE COMUNE ---
        $codiceComune = substr($cf, 11, 4);

        return [
            'cognome_codificato' => $cognome,
            'nome_codificato'    => $nome,
            'anno'               => $anno,
            'mese'               => $mese,
            'giorno'             => $giorno,
            'sesso'              => $sesso,
            'codice_comune'      => $codiceComune,
            'data_nascita'       => sprintf('%04d-%02d-%02d', $anno, $mese, $giorno)
        ];
    }
}
