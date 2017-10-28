<?php

require_once '/../utils/database.php';

class Sponsordata {

    private $sponsor_a;
    private $sponsor_b;
    private $income;

    public function __construct($sponsor_a, $sponsor_b) {
        $this->sponsor_a = $sponsor_a;
        $this->income = $sponsor_a;
        $this->sponsor_b = $sponsor_b;
    }
    
    public function teamWin() {
        $this->income += $this->sponsor_b;
    }

}

?>