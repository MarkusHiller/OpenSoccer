<?php

class TestSimulation {
	
    private $html;

	public function __construct() {
        $this->addHead();
	}

    public function getHtml() {
        $this->closeHtml();
        return $this->html;   
    }

    public function addResult($team1, $team2, $result) {
        $this->html .= '<tr><td>'.$team1.'</td><td>'.$team2.'</td><td>'.$result.'</td></tr>';
    }

    private function addHead() {
        $this->html = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"><html lang="de"><head><meta http-equiv="content-type" content="text/html; charset=utf-8"><title>Simulationtest</title></head><body>';
        $this->html .= '<table><tr><th>Team 1</th><th>Team2</th><th>Result</th></tr>';
    }

    private function closeHtml() {
        $this->html .= '</table></body></html>';
    }
	

}

?>