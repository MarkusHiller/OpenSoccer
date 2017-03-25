<?php

class TeamController {
    
    public static function getTeamNameByIds($ids) {
        $sql = "SELECT name FROM " . CONFIG_TABLE_PREFIX . "teams WHERE ids = '" . $ids . "'";
        $result = DB::query($sql, FALSE);
        $result = mysql_fetch_object($result);
        if (!$result) {
            //TODO:: Error log
        }
        
        return $result->name;
    }
    
    public static function resetTeam($ids, $ligaIds) {
        self::removePlayers($ids);
        self::addPlayers($ids, $ligaIds);
    }
    
    private static function removePlayers($teamIds) {
        $sql = "UPDATE " . CONFIG_TABLE_PREFIX . "spieler SET team = 'frei', liga = 'frei', vertrag = 0, startelf_Liga = 0, startelf_Pokal = 0, startelf_Cup = 0, startelf_Test = 0, transfermarkt = 0, leiher = 'keiner', frische = " . mt_rand(50, 100) . " WHERE team = '" . $teamIds . "'";
        DB::query($sql, false);
    }
    
    public static function addPlayers($teamIds, $liga) {
        $in14days = Utils::endOfDay(Utils::getTimestamp('+14 days'));
        $updateGoalkeeperSql = "UPDATE " . CONFIG_TABLE_PREFIX . "spieler SET team = '" . $teamIds . "', liga = '" . $liga . "', vertrag = " . $in14days . ", frische = " . mt_rand(50, 100) . ", startelf_Liga = 0, startelf_Pokal = 0, startelf_Cup = 0, startelf_Test = 0, gehalt = ROUND(marktwert/14) WHERE team = 'frei' AND wiealt < 10585 AND transfermarkt = 0 AND position = 'T' ORDER BY RAND() LIMIT 3";
        DB::query($updateGoalkeeperSql, false);
        
        $updateDefenseSql = "UPDATE " . CONFIG_TABLE_PREFIX . "spieler SET team = '" . $teamIds . "', liga = '" . $liga . "', vertrag = " . $in14days . ", frische = " . mt_rand(50, 100) . ", startelf_Liga = 0, startelf_Pokal = 0, startelf_Cup = 0, startelf_Test = 0, gehalt = ROUND(marktwert/14) WHERE team = 'frei' AND wiealt < 10585 AND transfermarkt = 0 AND position = 'A' ORDER BY RAND() LIMIT 7";
        DB::query($updateDefenseSql, false);
        
        $updateMidfeldSql = "UPDATE " . CONFIG_TABLE_PREFIX . "spieler SET team = '" . $teamIds . "', liga = '" . $liga . "', vertrag = " . $in14days . ", frische = " . mt_rand(50, 100) . ", startelf_Liga = 0, startelf_Pokal = 0, startelf_Cup = 0, startelf_Test = 0, gehalt = ROUND(marktwert/14) WHERE team = 'frei' AND wiealt < 10585 AND transfermarkt = 0 AND position = 'M' ORDER BY RAND() LIMIT 7";
        DB::query($updateMidfeldSql, false);
        
        $updateStrikerSql = "UPDATE " . CONFIG_TABLE_PREFIX . "spieler SET team = '" . $teamIds . "', liga = '" . $liga . "', vertrag = " . $in14days . ", frische = " . mt_rand(50, 100) . ", startelf_Liga = 0, startelf_Pokal = 0, startelf_Cup = 0, startelf_Test = 0, gehalt = ROUND(marktwert/14) WHERE team = 'frei' AND wiealt < 10585 AND transfermarkt = 0 AND position = 'S' ORDER BY RAND() LIMIT 4";
        DB::query($updateStrikerSql, false);
    }
    
    public function getSelectableTeams() {
        $resultModel = new ResultModel();
        
        $lastManagedTimeout = Utils::getTimestamp('-30 days');
        $sql = "SELECT a.ids, a.name, a.liga, b.name AS ligaName FROM " . CONFIG_TABLE_PREFIX . "teams AS a JOIN " . CONFIG_TABLE_PREFIX . "ligen AS b ON a.liga = b.ids WHERE a.ids NOT IN (SELECT team FROM " . CONFIG_TABLE_PREFIX . "users) AND a.last_managed > $lastManagedTimeout ORDER BY b.level ASC";
        $result = DB::query($sql, false);
        
        $resultModel->data = [];
        while ($team = mysql_fetch_assoc($result)) {
            $resultModel->data[] = $team;
        }
        
        $resultModel->err = false;
        echo json_encode($resultModel);
        return;
    }
    
}