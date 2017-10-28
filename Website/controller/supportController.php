<?php

require_once(__DIR__ . '/../utils/database.php');

class SupportController {

    public static function getUnreadedTicketCount($userIds, $status) {
        $supportSql = $status == 'Helfer' || $status == 'Admin' ? "" : " AND visibilityLevel = 0";
        $sql = "SELECT COUNT(*) FROM " . CONFIG_TABLE_PREFIX . "supportrequests WHERE open = 1 AND '" . $userIds . "' NOT IN (SELECT userId FROM " . CONFIG_TABLE_PREFIX . "supportread WHERE anfrageID = id)" . $supportSql;
        $result = DB::query($sql, FALSE);
        
        return mysql_result($result, 0);
    }

}
