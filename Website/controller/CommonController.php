<?php

class CommonController {
    
    public function GetTopmanager() {
        $resultModel = new TopmanagerResultModel();
        
        $sql = "SELECT a.ids, a.username, b.elo FROM " . CONFIG_TABLE_PREFIX . "users AS a JOIN " . CONFIG_TABLE_PREFIX . "teams AS b ON a.team = b.ids ORDER BY b.elo DESC LIMIT 0, 5";
        $result = DB::query($sql, true);
        
        $resultModel->setTeams($result);
        $resultModel->err = false;
        
        echo json_encode($resultModel);
        return;
    }
    
    public function GetInfocounts() {
        $resultModel = new ResultModel();
        
        $teamIds = $_SESSION['TeamIds'];
        $userIds = $_SESSION['UserIds'];
        $resultModel->data = new stdClass();
        $sql = "SELECT COUNT(*) FROM " . CONFIG_TABLE_PREFIX . "transfermarkt_leihe WHERE besitzer = '$teamIds' AND akzeptiert = 0";
        $result = DB::query($sql, false);
        $resultModel->data->loanCount = mysql_result($result, 0);
        
        $sql = "SELECT COUNT(*) FROM " . CONFIG_TABLE_PREFIX . "testspiel_anfragen WHERE team2 = '$teamIds'";
        $result = DB::query($sql, false);
        $resultModel->data->testgameCount = mysql_result($result, 0);
        
        $sql = "SELECT COUNT(*) FROM " . CONFIG_TABLE_PREFIX . "ligaChangeAnfragen WHERE anTeam = '$teamIds'";
        $result = DB::query($sql, false);
        $resultModel->data->ligachangeCount = mysql_result($result, 0);
        
        $resultModel->data->supportCount = SupportController::getUnreadedTicketCount($userIds, $_SESSION['status']);
        
        $resultModel->err = false;
        
        echo json_encode($resultModel);
        return;
    }
    
}