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

    public static function getChanceForOffer($marktwert, $angebot) {
        $chance = ($angebot / $marktwert - 1 + (0.3 / $marktwert * ($marktwert - ($angebot - $marktwert)))) * 100;
        if($chance > 0) return $chance;
        return 0;
    }

}
