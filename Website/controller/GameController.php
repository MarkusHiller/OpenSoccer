<?php

require_once(__DIR__ . '/TeamController.php');

class GameController {
    
    public function GetNextMatches() {
        $resultModel = new ResultModel();
        $teamname = TeamController::getTeamNameByIds($_SESSION['TeamIds']);
        $nxt3_zeit = mktime(0, 0, 0, date('m', time()), date('d', time()), date('Y', time()));
        $sql = "SELECT id, team1, team2, ergebnis, typ, datum FROM " . CONFIG_TABLE_PREFIX . "spiele WHERE (team1 = '" . $teamname . "' OR team2 = '" . $teamname . "') AND (datum > " . $nxt3_zeit . ") ORDER BY datum ASC LIMIT 0, 5";
        $result = DB::query($sql, true);
        
        $resultModel->data = [];
        while($game = mysql_fetch_object($result)) {
            $match = new ShortMatchInfo();
            $match->id = $game->id;
            $match->team = $game->team1 == $teamname ? $game->team2 : $game->team1;
            $match->location = $game->team1 == $teamname ? 'HOME' : '';
            $match->type = $game->typ;
            $match->result = $game->ergebnis;
            $resultModel->data[] = $match;
        }
        
        $resultModel->err = false;
        
        echo json_encode($resultModel);
        return;
    }
    
}