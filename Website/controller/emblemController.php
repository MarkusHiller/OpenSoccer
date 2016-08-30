<?php

require_once '/../utils/database.php';
require_once '/messageController.php';
require_once '/userController.php';

class EmblemController {
	
	public static function getEmblemByTeamIds($ids) {		
		$sql = "SELECT COUNT(*) FROM " . CONFIG_TABLE_PREFIX . "emblems WHERE team = '" . $ids . "' AND confirmed = 1 LIMIT 1";
        $result = DB::query($sql, FALSE);
        $count = mysql_result($result, 0);
        if (!$result) {
            //TODO:: Error log
        }

        if($count == 0) {
            return 'default.png';
        }

        $obj = mysql_fetch_object($result);
        return $ids.'.png';
	}

    public static function getUnconfirmedEmblems() {		
		$sql = "SELECT team FROM " . CONFIG_TABLE_PREFIX . "emblems WHERE confirmed = 0";
        $result = DB::query($sql, FALSE);
        if (!$result) {
            //TODO:: Error log
        }

        return $result;
	}

    public static function countUnconfirmedEmblems() {
        $sql = "SELECT COUNT(*) FROM " . CONFIG_TABLE_PREFIX . "emblems WHERE confirmed = 0";
        $result = DB::query($sql, FALSE);
        $count = mysql_result($result, 0);
        if (!$result) {
            //TODO:: Error log
        }

        return $count;
    }

    public static function handleConfirm($confirm, $ids) {		
		if($confirm == 'true') {
            $sql = "UPDATE ". CONFIG_TABLE_PREFIX . "emblems SET confirmed = 1 WHERE team = '" . $ids . "'";
            DB::query($sql, TRUE);
        } else {
            $sql = "DELETE FROM ". CONFIG_TABLE_PREFIX . "emblems WHERE team = '" . $ids . "'";
            DB::query($sql, TRUE);
            unlink(realpath(dirname(__FILE__)) . '/../images/emblems/' . $ids . '.png');
            $msg = __('Dein Wappen entsprach nicht den <a href="/regeln.php">Regeln</a> und wurde daher gelöscht. Bitte schaue nochmal in den <a href="/regeln.php">Regeln</a> nach was erlaubt ist und was nicht.');
            MessageController::addOfficialPn(UserController::getIdsByTeamIds($ids), __('Wappen nicht freigegeben'), $msg);
        }
	}

    public static function deleteEmblemByTeamIds($ids) {
        self::handleConfirm('false', $ids);
    }

    public static function saveEmblemForTeamIds($ids) {
        if($_FILES['emblem']['error'] != 0) {
            switch($_FILES['emblem']['error']) {
                case 2:
                    return _('Die Datei ist zu groß. Ein Wappen darf maximal ein Megabyte groß sein.');
                case 4:
                    return _('Bitte zuerst eine Datei auswählen.');
                default:
                    return _('Es ist ein Fehler aufgetreten, bitte versuche es später erneut.');
            }
        } else if($_FILES['emblem']['type'] != 'image/png') {
            return _('Es sind nur png Dateien erlaubt.');
        }
        $uploaddir = realpath(dirname(__FILE__)) . '/../images/emblems/';
        $uploadfile = $uploaddir . $ids . '.png';
        move_uploaded_file($_FILES['emblem']['tmp_name'], $uploadfile);
        
        $sql = "INSERT INTO ". CONFIG_TABLE_PREFIX . "emblems (team) VALUES ('" . $ids . "') ON DUPLICATE KEY UPDATE confirmed = 0";
        DB::query($sql, FALSE);
        return _('Dein Wappen wurde erfolgreich hochgeldaen. Um sicherzugehen, dass es nicht gegen die Regeln verstößt, muss es erst vom Support freigegeben werden.');
    }
	
}