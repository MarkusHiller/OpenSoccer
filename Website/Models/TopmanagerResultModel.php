<?php
class TopmanagerResultModel {

    public $err;
    
    public $teams;

    public function setTeams($teams) {
        while ($team = mysql_fetch_object($teams)) {
            $this->teams[] = $team;
        }
    }

}