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
}