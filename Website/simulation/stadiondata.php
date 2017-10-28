<?php

require_once '/../utils/database.php';
require_once '/teamdata.php';

class Stadiondata {

    private $team1;
    private $team2;
    private $type;
    private $data;
    private $fanaufkommen;
    private $liga;
    private $incomeTeam1;
    private $incomeTeam2;
    private $gamereport;

    public function __construct($team1, $team2, $type, $liga, $gamereport) {
        $this->team1 = $team1;
        $this->team2 = $team2;
        $this->type = $type;
        $this->liga = $liga;
        $this->gamereport = $gamereport;
        $this->loadData();
        $this->addVisitorsTeam2();
        $this->addTypeSpezificFans();
        $this->addLowCoastFans();
        $this->addDerbyFans();
        $this->removeFansWithNoPlace();
        $this->calcIncome();
        $this->addStadiondataToGamereport();
    }

    public function getVisitors() {
        return $this->fanaufkommen;
    }

    private function loadData() {
        $sql = "SELECT name, plaetze, preis FROM " . CONFIG_TABLE_PREFIX . "stadien WHERE team = '" . $this->team1->teamIds . "'";
        $result = DB::query($sql);
        $this->data = mysql_fetch_object($result);
    }

    private function addVisitorsTeam2() {
        $this->fanaufkommen = $this->team1->fanaufkommen + 15000 / pow(1.4, ($this->team2->rank - 1));
    }

    private function addTypeSpezificFans() {
        switch ($this->type) {
            case 'Pokal': $this->fanaufkommen += 30000;
                break;
            case 'Cup': $this->fanaufkommen += 10000;
                break;
            case 'Liga': $this->fanaufkommen += 15000;
                break;
            default: $this->fanaufkommen += 5000;
                break;
        }
    }

    private function addLowCoastFans() {
        $this->fanaufkommen += (70 - $this->data->preis) * 750;
    }

    private function removeFansWithNoPlace() {
        $this->fanaufkommen = intval(min($this->data->plaetze, $this->fanaufkommen));
    }

    private function calcIncome() {
        if ($this->type == 'Test') {
            $this->incomeTeam1 = 0;
            $this->incomeTeam2 = 0;
        } elseif ($this->liga == 'Pokal_Runde_5' OR $this->type == 'Cup') {
            $this->incomeTeam1 = round($this->fanaufkommen * $this->data->preis / 2);
            $this->incomeTeam2 = round($this->fanaufkommen * $this->data->preis / 2);
        } else {
            $this->incomeTeam1 = round($this->fanaufkommen * $this->data->preis);
            $this->incomeTeam2 = 0;
        }
    }

    private function addStadiondataToGamereport() {
        $this->gamereport->addStadionPart($this->data->name, $this->fanaufkommen, $this->data->preis);
    }

}
