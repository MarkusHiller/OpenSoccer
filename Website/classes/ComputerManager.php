<?php

require_once(__DIR__ . '/../utils/database.php');
require_once(__DIR__ . '/../utils/utils.php');
require_once(__DIR__ . '/../Logging/Log.php');

/**
 * Description of ComputerManager
 *
 * @author MarkusPC
 */
class ComputerManager {

    const COMPUTER_MINIMUM_KONTOSTAND = 20000000;
    const PLAYER_MORAL = 60;
    const CONTRACT_MAX_AGE_HOLIDAY = 13140; //36 years
    const CONTRACT_MAX_AGE_COMPUTER = 10585; //29 years
    const MAX_PLAYER_T = 3;
    const MAX_PLAYER_A = 7;
    const MAX_PLAYER_M = 7;
    const MAX_PLAYER_S = 4;

    private $in14days;
    private $sqlIsDemoTeam = "";
    private $sqlHolidayTeams = "";
    private $beurlaubte_teams = array();

    function __construct() {
        $this->in14days = Utils::endOfDay(Utils::getTimestamp('+14 days'));
        $this->getDemoTeam();
        $this->getUserInHoliday();
        $this->getTeamsToManage();
    }

    private function getDemoTeam() {
        $sql = "SELECT team FROM " . CONFIG_TABLE_PREFIX . "users WHERE ids = '" . CONFIG_DEMO_USER . "'";
        $result = DB::query($sql, false);
        if (mysql_num_rows($result) == 1) {
            $demoTeamIds = mysql_result($result, 0);
            $this->sqlIsDemoTeam = " OR ids = '" . mysql_real_escape_string($demoTeamIds) . "'";
        }
    }

    private function getUserInHoliday() {
        $sql = "SELECT user, team FROM " . CONFIG_TABLE_PREFIX . "urlaub WHERE ende > " . time();
        $result = DB::query($sql, false);
        $urlaub_string = "('LEER', ";
        while ($urlaub3 = mysql_fetch_assoc($result)) {
            if ($urlaub3['user'] != CONFIG_DEMO_USER) {
                $urlaub_string .= "'" . $urlaub3['user'] . "', ";
                $this->beurlaubte_teams[] = $urlaub3['team'];
            }
        }
        $urlaub_string2 = substr($urlaub_string, 0, -2);
        $urlaub_string2 .= ")";
        $this->sqlHolidayTeams = $urlaub_string2;
    }

    private function getTeamsToManage() {
        $sql = "SELECT name, ids, liga FROM " . CONFIG_TABLE_PREFIX . "teams WHERE ids NOT IN (SELECT team FROM " . CONFIG_TABLE_PREFIX . "users WHERE ids NOT IN " . $this->sqlHolidayTeams . ")" . $this->sqlIsDemoTeam . " ORDER BY last_managed ASC LIMIT 0, 8";
        $result = DB::query($sql, false);
        while ($data = mysql_fetch_object($result)) {
            $this->manageTeam($data);
        }
    }

    private function manageTeam($data) {
        if (!in_array($data->ids, $this->beurlaubte_teams)) {
            $this->manageComputerTeam($data);
        } else {
            $this->manageHolidayTeam($data);
        }
    }

    private function manageComputerTeam($data) {
        $this->handlePlayer($data->ids);
        $this->extendContract($data->ids, true, self::CONTRACT_MAX_AGE_COMPUTER);
        $this->handleTactics($data->ids);
        $this->updateBankBalance($data->ids);
        $this->setJugendarbeit($data->ids, $data->liga);
        $this->setLastManaged($data->ids);
    }

    private function manageHolidayTeam($data) {
        $this->extendContract($data->ids, false, self::CONTRACT_MAX_AGE_HOLIDAY);
        $this->updateNomination($data->ids);
        $this->setLastManaged($data->ids);
    }

    private function handlePlayer($teamIds) {
        $this->dismissOldPlayer($teamIds);
        $goalkeeperCount = $this->handleGoalkeeper($teamIds);
        $defenseCount = $this->handleDefense($teamIds);
        $midfeldCount = $this->handleMidfield($teamIds);
        $strikerCount = $this->handleStriker($teamIds);
        $this->selectPosToSearch($teamIds, $goalkeeperCount, $defenseCount, $midfeldCount, $strikerCount);
        $this->updateNomination($teamIds);
    }

    private function dismissOldPlayer($teamIds) {
        $sql = "UPDATE " . CONFIG_TABLE_PREFIX . "spieler SET team = 'frei', liga = 'frei', vertrag = 0, startelf_Liga = 0, startelf_Pokal = 0, startelf_Cup = 0, startelf_Test = 0, transfermarkt = 0, leiher = 'keiner', frische = " . mt_rand(50, 100) . " WHERE team = '" . $teamIds . "' AND wiealt > " . self::CONTRACT_MAX_AGE_COMPUTER;
        DB::query($sql, false);
    }

    private function handleGoalkeeper($teamIds) {
        $sql = "SELECT * FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE team = '" . $teamIds . "' AND position = 'T'";
        $result = DB::query($sql, false);
        $goalkeeperCount = mysql_num_rows($result);
        if ($goalkeeperCount > self::MAX_PLAYER_T) {
            $this->selectPlayerToDismiss($result);
            $goalkeeperCount--;
        }
        return $goalkeeperCount;
    }

    private function handleDefense($teamIds) {
        $sql = "SELECT * FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE team = '" . $teamIds . "' AND position = 'A'";
        $result = DB::query($sql, false);
        $defenseCount = mysql_num_rows($result);
        if ($defenseCount > self::MAX_PLAYER_A) {
            $this->selectPlayerToDismiss($result);
            $defenseCount--;
        }
        return $defenseCount;
    }

    private function handleMidfield($teamIds) {
        $sql = "SELECT * FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE team = '" . $teamIds . "' AND position = 'M'";
        $result = DB::query($sql, false);
        $midfieldCount = mysql_num_rows($result);
        if ($midfieldCount > self::MAX_PLAYER_M) {
            $this->selectPlayerToDismiss($result);
            $midfieldCount--;
        }
        return $midfieldCount;
    }

    private function handleStriker($teamIds) {
        $sql = "SELECT * FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE team = '" . $teamIds . "' AND position = 'S'";
        $result = DB::query($sql, false);
        $strikerCount = mysql_num_rows($result);
        if ($strikerCount > self::MAX_PLAYER_S) {
            $this->selectPlayerToDismiss($result);
            $strikerCount--;
        }
        return $strikerCount;
    }

    private function selectPlayerToDismiss($players) {
        $playerToDismiss = mysql_fetch_object($players);
        while ($player = mysql_fetch_object($players)) {
            $talentDiff = $player->talent - $playerToDismiss->talent;
            if ($talentDiff > 0.4) {
                continue;
            } else if ($talentDiff >= 0) {
                $strengthDiff = $player->staerke - $playerToDismiss->staerke;
                $playerToDismiss = $strengthDiff < 0 ? $player : $playerToDismiss;
            } else {
                $playerToDismiss = $player;
            }
        }
        $this->dismissPlayer($playerToDismiss);
    }

    private function dismissPlayer($player) {
        $sql = "UPDATE " . CONFIG_TABLE_PREFIX . "spieler SET team = 'frei', liga = 'frei', vertrag = 0, startelf_Liga = 0, startelf_Pokal = 0, startelf_Cup = 0, startelf_Test = 0, transfermarkt = 0, leiher = 'keiner', frische = " . mt_rand(50, 100) . " WHERE ids = '" . $player->ids . "'";
        DB::query($sql, false);
    }

    private function selectPosToSearch($teamIds, $goalkeeperCount, $defenceCount, $midfeldCount, $strikerCount) {
        if ($goalkeeperCount < self::MAX_PLAYER_T) {
            $this->setPosToSearch($teamIds, "T");
            return;
        } else if ($defenceCount < self::MAX_PLAYER_A) {
            $this->setPosToSearch($teamIds, "A");
            return;
        } else if ($midfeldCount < self::MAX_PLAYER_M) {
            $this->setPosToSearch($teamIds, "M");
            return;
        } else if ($strikerCount < self::MAX_PLAYER_S) {
            $this->setPosToSearch($teamIds, "S");
            return;
        }

        $avgTalentT = $this->getAvgByPos($teamIds, "T");
        $avgTalentS = $this->getAvgByPos($teamIds, "S");
        $avgTalentA = $this->getAvgByPos($teamIds, "A");
        $avgTalentM = $this->getAvgByPos($teamIds, "M");

        if ($avgTalentT < min([$avgTalentS, $avgTalentA, $avgTalentM])) {
            $this->setPosToSearch($teamIds, "T");
        } else if ($avgTalentS < min([$avgTalentA, $avgTalentM])) {
            $this->setPosToSearch($teamIds, "S");
        } else if ($avgTalentA < $avgTalentM) {
            $this->setPosToSearch($teamIds, "A");
        } else {
            $this->setPosToSearch($teamIds, "M");
        }
    }

    private function getAvgByPos($teamIds, $pos) {
        $sql = "SELECT AVG(talent) FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE team = '" . $teamIds . "' AND position = '" . $pos . "'";
        $result = DB::query($sql, false);
        return mysql_result($result, 0);
    }

    private function setPosToSearch($teamIds, $pos) {
        $sql = "UPDATE " . CONFIG_TABLE_PREFIX . "teams SET posToSearch = '" . $pos . "' WHERE ids = '" . $teamIds . "'";
        DB::query($sql, false);
    }

    private function setJugendarbeit($teamIds, $liga) {
        if (GameTime::getMatchDay() == 1) {
            $sql = "UPDATE " . CONFIG_TABLE_PREFIX . "teams SET jugendarbeit = (SELECT (6 - SUBSTRING(name, -1, 1)) FROM " . CONFIG_TABLE_PREFIX . "ligen WHERE ids = '" . $liga . "') WHERE ids = '" . $teamIds . "'";
            DB::query($sql, false);
        }
    }

    private function extendContract($teamIds, $updateMoral, $contractMaxAge) {
        $moralSql = "";
        if ($updateMoral) {
            $moralSql = ", moral = 60";
        }
        $sql = "UPDATE " . CONFIG_TABLE_PREFIX . "spieler SET vertrag = " . $this->in14days . ", gehalt = ROUND(marktwert/14)" . $moralSql . " WHERE team = '" . $teamIds . "' AND vertrag < " . $this->in14days . " AND wiealt < " . $contractMaxAge;
        DB::query($sql, false);
    }

    private function updateNomination($teamIds) {
        $sql = "UPDATE " . CONFIG_TABLE_PREFIX . "spieler SET startelf_Liga = 0, startelf_Pokal = 0, startelf_Cup = 0 WHERE team = '" . $teamIds . "'";
        DB::query($sql, false);
        $this->nominateGoalkeeper($teamIds);
        $this->nominateDefence($teamIds);
        $this->nominateMidfeld($teamIds);
        $this->nominateStriker($teamIds);
        $this->calcNomination($teamIds);
    }

    private function nominateGoalkeeper($teamIds) {
        $sql = "UPDATE " . CONFIG_TABLE_PREFIX . "spieler SET startelf_Liga = 11, startelf_Pokal = 11, startelf_Cup = 11 WHERE team = '" . $teamIds . "' AND position = 'T' AND verletzung = 0 ORDER BY (staerke*frische) DESC LIMIT 1";
        DB::query($sql, false);
    }

    private function nominateDefence($teamIds) {
        $select = "SELECT ids FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE team = '" . $teamIds . "' AND position = 'A' AND verletzung = 0 ORDER BY (staerke*frische) DESC LIMIT 4";
        $result = DB::query($select, false);
        for ($i = 0; $i < 4; $i++) {
            $ids = mysql_result($result, $i);
            if ($ids != "") {
                $sql = "UPDATE " . CONFIG_TABLE_PREFIX . "spieler SET startelf_Liga = " . ($i + 7) . ", startelf_Pokal = " . ($i + 7) . ", startelf_Cup = " . ($i + 7) . " WHERE ids = '" . $ids . "'";
                DB::query($sql, false);
            } else {
                $logMsg = "Not enough players for position A Team: ".$teamIds;
                Log::logToFile("ComputerManager", $logMsg);
            }
        }
    }

    private function nominateMidfeld($teamIds) {
        $select = "SELECT ids FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE team = '" . $teamIds . "' AND position = 'M' AND verletzung = 0 ORDER BY (staerke*frische) DESC LIMIT 4";
        $result = DB::query($select, false);
        for ($i = 0; $i < 4; $i++) {
            $ids = mysql_result($result, $i);
            if ($ids != "") {
                $sql = "UPDATE " . CONFIG_TABLE_PREFIX . "spieler SET startelf_Liga = " . ($i + 3) . ", startelf_Pokal = " . ($i + 3) . ", startelf_Cup = " . ($i + 3) . " WHERE ids = '" . $ids . "'";
                DB::query($sql, false);
            } else {
                $logMsg = "Not enough players for position M Team: ".$teamIds;
                Log::logToFile("ComputerManager", $logMsg);
            }
        }
    }

    private function nominateStriker($teamIds) {
        $select = "SELECT ids FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE team = '" . $teamIds . "' AND position = 'S' AND verletzung = 0 ORDER BY (staerke*frische) DESC LIMIT 2";
        $result = DB::query($select, false);
        for ($i = 0; $i < 2; $i++) {
            $ids = mysql_result($result, $i);
            if ($ids != "") {
                $sql = "UPDATE " . CONFIG_TABLE_PREFIX . "spieler SET startelf_Liga = " . ($i + 1) . ", startelf_Pokal = " . ($i + 1) . ", startelf_Cup = " . ($i + 1) . " WHERE ids = '" . $ids . "'";
                DB::query($sql, false);
            } else {
                $logMsg = "Not enough players for position S Team: ".$teamIds;
                Log::logToFile("ComputerManager", $logMsg);
            }
        }
    }

    private function calcNomination($teamIds) {
        $sql = "UPDATE " . CONFIG_TABLE_PREFIX . "teams SET aufstellung = (SELECT SUM(staerke) FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE team = '" . $teamIds . "' AND startelf_Liga > 0) WHERE ids = '" . $teamIds . "'";
        DB::query($sql, false);
    }

    private function handleTactics($teamIds) {
        if (Utils::Chance_Percent(20)) {
            $sql = "UPDATE " . CONFIG_TABLE_PREFIX . "taktiken SET ausrichtung = " . rand(1, 3) . ", geschw_auf = " . rand(1, 3) . ", pass_auf = " . rand(1, 3) . ", risk_pass = " . rand(1, 3) . ", druck = " . rand(1, 3) . ", aggress = " . rand(1, 3) . " WHERE team = '" . $teamIds . "'";
            DB::query($sql, false);
        }
    }

    private function updateBankBalance($teamIds) {
        $sql = "UPDATE " . CONFIG_TABLE_PREFIX . "teams SET konto = " . self::COMPUTER_MINIMUM_KONTOSTAND . " WHERE ids = '" . $teamIds . "' AND konto < " . self::COMPUTER_MINIMUM_KONTOSTAND;
        DB::query($sql, false);
    }

    private function setLastManaged($teamIds) {
        $sql = "UPDATE " . CONFIG_TABLE_PREFIX . "teams SET last_managed = " . time() . " WHERE ids = '" . $teamIds . "'";
        DB::query($sql, false);
    }

}
