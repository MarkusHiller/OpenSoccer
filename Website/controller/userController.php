<?php

require_once '/../utils/database.php';

class UserController {
	
	public static function getManagerLinkByTeamIds($ids) {		
		$sql = "SELECT username, ids FROM " . CONFIG_TABLE_PREFIX . "users WHERE team = '" . $ids . "'";
        $result = DB::query($sql, FALSE);
        $result = mysql_fetch_object($result);
        if (!$result) {
            //TODO:: Error log
        }

        return self::createManagerLink($result->username, $result->ids);
	}

    public static function getIdsByTeamIds($ids) {		
		$sql = "SELECT ids FROM " . CONFIG_TABLE_PREFIX . "users WHERE team = '" . $ids . "'";
        $result = DB::query($sql, FALSE);
        $result = mysql_fetch_object($result);
        if (!$result) {
            //TODO:: Error log
        }

        return $result->ids;
	}

    private static function createManagerLink($username, $ids) {
        if (substr($username, 0, 9) == 'GELOESCHT') {
            return 'Gelöschter User';
        }
        else {
            if ($ids == '') {
                return $username;
            }
            else {
                return '<a href="/manager.php?id='.$ids.'">'.$username.'</a>';
            }
        }
    }

}