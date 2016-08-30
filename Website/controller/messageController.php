<?php

require_once '/../utils/database.php';

class MessageController {
	
	public static function addOfficialPn($ids, $title, $msg) {		
		$sql = "INSERT INTO " . CONFIG_TABLE_PREFIX . "pn (ids, von, an, titel, inhalt, zeit, in_reply_to) VALUES (MD5(now()), '".CONFIG_OFFICIAL_USER."', '".$ids."', '" . $title . "', '" . $msg . "', '".time()."', '')";
        $result = DB::query($sql, FALSE);
        if (!$result) {
            //TODO:: Error log
        }
	}

}