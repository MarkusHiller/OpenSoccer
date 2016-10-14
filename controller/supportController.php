<?php

require_once './utils/database.php';

class SupportController {

    public static function getUnreadedTicketCount($userIds, $status) {
        $supportSql = $status == 'Helfer' || $status == 'Admin' ? "" : " AND visibilityLevel = 0";
        $sql = "SELECT COUNT(*) FROM " . CONFIG_TABLE_PREFIX . "supportRequests WHERE open = 1 AND '" . $userIds . "' NOT IN (SELECT userId FROM " . CONFIG_TABLE_PREFIX . "supportRead WHERE anfrageID = id)" . $supportSql;
        $result = DB::query($sql, FALSE);
        
        return mysql_result($result, 0);
    }

}
