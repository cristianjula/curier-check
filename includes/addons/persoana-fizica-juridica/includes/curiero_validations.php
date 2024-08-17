<?php

if (!function_exists('curiero_cnp_validate')) {
    function curiero_cnp_validate(string $cnp): bool
    {
        // CNP must have 13 characters
        if (strlen($cnp) != 13) {
            return false;
        }

        if ($cnp != (int) $cnp) {
            return false;
        }

        $cnp = str_split($cnp);

        $hashTable = [2, 7, 9, 1, 4, 6, 3, 5, 8, 2, 7, 9];
        $hashResult = 0;

        // All characters must be numeric
        for ($i = 0; $i < 12; ++$i) {
            $hashResult += (int) $cnp[$i] * $hashTable[$i];
        }

        $hashResult = $hashResult % 11;
        if ($hashResult == 10) {
            $hashResult = 1;
        }

        if ($cnp[12] != $hashResult) {
            return false;
        }

        // Check Year
        $year = ($cnp[1] * 10) + $cnp[2];
        switch ($cnp[0]) {
            case 1:
            case 2:
                $year += 1900;

                break; // cetateni romani nascuti intre 1 ian 1900 si 31 dec 1999
            case 3:
            case 4:
                $year += 1800;

                break; // cetateni romani nascuti intre 1 ian 1800 si 31 dec 1899
            case 5:
            case 6:
                $year += 2000;

                break; // cetateni romani nascuti intre 1 ian 2000 si 31 dec 2099
            case 7:
            case 8:
            case 9:                 // rezidenti si Cetateni Straini
                $year += 2000;
                if ($year > (int) date('Y') - 14) {
                    $year -= 100;
                }

                break;
            default:
                return false;

                break;
        }

        if ($year > 1800 && $year < 2099) {
            return true;
        }

        return false;
    }
}

if (!function_exists('curiero_iban_validate')) {
    function curiero_iban_validate(string $check): bool
    {
        if (
            !is_string($check)
            || !preg_match('/^[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}$/', $check)
        ) {
            return false;
        }

        $country = substr($check, 0, 2);
        $checkInt = (int) (substr($check, 2, 2));
        $account = substr($check, 4);
        $search = range('A', 'Z');
        $replace = [];
        foreach (range(10, 35) as $tmp) {
            $replace[] = (string) $tmp;
        }
        $numStr = str_replace($search, $replace, $account . $country . '00');
        $checksum = (int) (substr($numStr, 0, 1));
        $numStrLength = strlen($numStr);
        for ($pos = 1; $pos < $numStrLength; ++$pos) {
            $checksum *= 10;
            $checksum += (int) (substr($numStr, $pos, 1));
            $checksum %= 97;
        }

        return $checkInt === 98 - $checksum;
    }
}

if (!function_exists('curiero_cif_validate')) {
    function curiero_cif_validate(string $cif): bool
    {
        // Daca este string, elimina atributul fiscal si spatiile
        if (!is_int($cif)) {
            $cif = strtoupper($cif);
            if (strpos($cif, 'RO') === 0) {
                $cif = substr($cif, 2);
            }
            $cif = (int) trim($cif);
        }

        // daca are mai mult de 10 cifre sau mai putin de 2, nu-i valid
        if (strlen($cif) > 10 || strlen($cif) < 2) {
            return false;
        }
        // numarul de control
        $v = 753217532;

        // extrage cifra de control
        $c1 = $cif % 10;
        $cif = (int) ($cif / 10);

        // executa operatiile pe cifre
        $t = 0;
        while ($cif > 0) {
            $t += ($cif % 10) * ($v % 10);
            $cif = (int) ($cif / 10);
            $v = (int) ($v / 10);
        }

        // aplica inmultirea cu 10 si afla modulo 11
        $c2 = $t * 10 % 11;

        // daca modulo 11 este 10, atunci cifra de control este 0
        if ($c2 == 10) {
            $c2 = 0;
        }

        return $c1 === $c2;
    }
}
