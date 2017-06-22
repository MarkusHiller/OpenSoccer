<?php

require_once(__DIR__ . "/../Helpers/utils.php");

class ApiController {

    public function updateAufstellung() {
        $result = new stdClass();
        $type = $_POST['type'];
        $data = $_POST['data'];var_dump($_POST);
        if (!$this->validateOncePerPosition($type, $data)) {
            $result->err = true;
            echo json_encode($result);
            return;
        }
        $this->updateAufstellungInDatabase($type, $data);
        if ($type == 'Cup') {
            if (Utils::setTaskDone('lineup_cup')) {
                $result->taskDone = true;
            }
        }
        $result->err = false;
        echo json_encode($result);
        return;
    }

    public function takeAufstellung() {
        $result = new stdClass();
        $from = $_POST['from'];
        $to = $_POST['to'];
        $team = $_SESSION['team'];

        $sql = "UPDATE " . CONFIG_TABLE_PREFIX . "spieler SET startelf_" . $to . " = startelf_" . $from . " WHERE team = '" . $team . "'";
        DB::query($sql, false);
        $this->createAufstellungsLog($to, $team);
        if ($to == 'Cup') {
            if (Utils::setTaskDone('lineup_cup')) {
                $result->taskDone = true;
            }
        }
        $result->err = false;
        echo json_encode($result);
        return;
    }

    private function validateOncePerPosition($type, $data) {
        $pos_array = array();
        foreach ($data as $player) {
            if ($player["startelf_" . $type] != 0) {
                $pos_array[$player["startelf_" . $type]] = array_key_exists($player["startelf_" . $type], $pos_array) ? $pos_array[$player["startelf_" . $type]] + 1 : 1;
            }
        }
        $max = max(array_values($pos_array));
        if ($max > 1) {
            return false;
        }
        return true;
    }

    private function updateAufstellungInDatabase($type, $data) {
        $team = $_SESSION['team'];
        foreach ($data as $player) {
            $sql = "UPDATE " . CONFIG_TABLE_PREFIX . "spieler SET startelf_" . $type . " = " . $player['startelf_' . $type] . " WHERE ids = '" . $player['ids'] . "' AND team ='" . $team . "'";
            DB::query($sql, false);
        }
        $this->createAufstellungsLog($type, $team);
    }

    private function createAufstellungsLog($type, $team) {
        $sql = "INSERT INTO " . CONFIG_TABLE_PREFIX . "aufstellungLog (team, zeit, typ) VALUES ('" . $team . "', " . time() . ", '" . $type . "')";
        DB::query($sql, false);
    }

}
