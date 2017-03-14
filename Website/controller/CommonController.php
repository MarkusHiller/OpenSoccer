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
   
}