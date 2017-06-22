<?php

class Utils {
    
    public static function Chance_Percent($chance, $universe = 100) {
        $chance = abs(intval($chance));
        $universe = abs(intval($universe));
        if (mt_rand(1, $universe) <= $chance) {
            return true;
        }
        return false;
    }
    
    public static function endOfDay($stempel) {
        return mktime(23, 59, 59, date('m', $stempel), date('d', $stempel), date('Y', $stempel));
    }
    
    public static function getTimestamp($shift = '', $startTime = -1) {
        if ($startTime == -1) {
            $dateTime = new DateTime(); // construct DateTime object with current time
        } else {
            $startTime = round($startTime);
            $dateTime = new DateTime('@' . $startTime); // construct DateTime object based on given timestamp
        }
        $dateTime->setTimeZone(new DateTimeZone('Europe/Berlin')); // timezone 408: Europe/Berlin
        if ($shift != '') { // if a time shift is set (e.g.: +1 month)
            $dateTime->modify($shift); // shift the time
        }
        return $dateTime->format('U'); // return the UNIX timestamp
    }
    
    public static function setTaskDone($shortName) {
        $teamIds = $_SESSION['team'];
        $userId = $_SESSION['userid'];
        if ($_SESSION['hasLicense'] == 0 && $teamIds != '__' . $userId) {
            $taskDone1 = "INSERT INTO " . CONFIG_TABLE_PREFIX . "licenseTasks_Completed (user, task) VALUES ('" . $userId . "', '" . mysql_real_escape_string(trim($shortName)) . "')";
            $taskDone2 = mysql_query($taskDone1);
            if ($taskDone2 != FALSE) {
                $getTaskMoney1 = "UPDATE " . CONFIG_TABLE_PREFIX . "teams SET konto = konto+1000000 WHERE ids = '" . $teamIds . "'";
                mysql_query($getTaskMoney1);
                $taskBuchung1 = "INSERT INTO " . CONFIG_TABLE_PREFIX . "buchungen (team, verwendungszweck, betrag, zeit) VALUES ('" . $teamIds . "', 'Manager-Prüfung', 1000000, " . time() . ")";
                mysql_query($taskBuchung1);
                return TRUE;
            }
            return FALSE;
        }
    }
    
    public static function getUserIP() {
        if (isset($_SERVER['REMOTE_ADDR'])) {
            return md5($_SERVER['REMOTE_ADDR']);
        } else {
            return 'd41d8cd98f00b204e9800998ecf8427e';
        }
    }
    
    public static function bigintval($value) {
        $value = trim($value);
        if (ctype_digit($value)) {
            return $value;
        }
        $value = preg_replace("/[^0-9](.*)$/", '', $value);
        if (ctype_digit($value)) {
            return $value;
        }
        return 0;
    }
    
    public static function einsatz_in_auktionen($teamIds) {
        $sql1 = "SELECT SUM(betrag_highest) AS einsatz FROM ". CONFIG_TABLE_PREFIX ."transfermarkt WHERE bieter_highest = '".$teamIds."'";
        $sql2 = mysql_query($sql1);
        if (mysql_num_rows($sql2) == 0) { return 0; }
        $sql3 = mysql_fetch_assoc($sql2);
        return intval($sql3['einsatz']);
    }
    
    public static function schaetzungVomScout($cookie_team, $cookie_scout, $spielerID, $spielerTalent, $spielerStaerke) {
        $possibleMD5chr = array(48=>-1, 49=>1, 50=>-1, 51=>1, 52=>-1, 53=>1, 54=>-1, 55=>1, 56=>-1, 57=>1, 97=>-1, 98=>1, 99=>-1, 100=>1, 101=>-1, 102=>1);
        if ($spielerStaerke == $spielerTalent) { return $spielerTalent; }
        $scout_hash = md5($cookie_team.$cookie_scout.$spielerID);
        $scout_hash_zahl = ord($scout_hash);
        if ($possibleMD5chr[$scout_hash_zahl] == 1) {
            $abweichung_max = 0.35-$cookie_scout*0.05;
        }
        else {
            $abweichung_max = -0.35+$cookie_scout*0.05;
        }
        $scout_hash = substr($scout_hash, 16);
        $scout_hash_zahl = ord($scout_hash);
        $zufallszahl_abweichung = $scout_hash_zahl-47;
        if ($zufallszahl_abweichung > 10) {
            $zufallszahl_abweichung = $zufallszahl_abweichung-39;
        }
        // $zufallszahl_abweichung ist jetzt eine Zufallszahl zwischen 1 und 16
        $abweichung_pro_zufallszahl = $abweichung_max/16;
        $abweichung = 1+$zufallszahl_abweichung*$abweichung_pro_zufallszahl;
        $schaetzung_des_scouts = round(($spielerTalent*$abweichung), 1);
        if ($schaetzung_des_scouts > 9.9) { $schaetzung_des_scouts = 9.9; }
        if ($schaetzung_des_scouts < $spielerStaerke) { $schaetzung_des_scouts = $spielerStaerke; }
        return $schaetzung_des_scouts;
    }
}