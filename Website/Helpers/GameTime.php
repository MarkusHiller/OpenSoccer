<?php


class GameTime {

    const MATCH_DAYS_PER_SEASON = 22;

    private static $season;
    private static $matchDay;

    public static function init() {
        $installDate = strtotime(CONFIG_INSTALL_DATE.' 12:00:00');
        // if it's summer time (DST) right now
        if (date('I') != 1) { $installDate += 3600; }
        $daysPassed = round((Utils::getTimestamp() - $installDate) / 86400);
        self::$season = floor($daysPassed / self::MATCH_DAYS_PER_SEASON);
        self::$matchDay = $daysPassed - self::$season * self::MATCH_DAYS_PER_SEASON + 1;
    }

    public static function getSeason() {
        return self::$season;
    }

    public static function getMatchDay() {
        return self::$matchDay;
    }

}
GameTime::init();