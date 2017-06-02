<?php

require_once(__DIR__ . '/../utils/database.php');

class MessageController {

    public static function addOfficialPn($ids, $title, $msg) {
        $id = md5(time().$ids);
        $sql = "INSERT INTO " . CONFIG_TABLE_PREFIX . "pn (ids, von, an, titel, inhalt, zeit, in_reply_to) VALUES ('" . $id . "', '" . CONFIG_OFFICIAL_USER . "', '" . $ids . "', '" . $title . "', '" . $msg . "', '" . time() . "', '')";
        $result = DB::query($sql, FALSE);
        if (!$result) {
            //TODO:: Error log
        }
    }

}
