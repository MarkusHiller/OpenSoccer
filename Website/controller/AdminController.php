<?php

class AdminController {

    public function createPlayers() {
        if (!isset($_SESSION['status']) OR $_SESSION['status'] != 'Admin') {
            header("HTTP/1.1 401 Unauthorized");
            exit;
        }

        $pos = $_POST['position'];
        $vor1 = "SELECT name FROM " . CONFIG_TABLE_PREFIX . "namen_pool WHERE typ = 1";
        $vor2 = DB::query($vor1, false);
        $vor2a = mysql_num_rows($vor2) - 1;
        $vornamen = array();
        while ($vor3 = mysql_fetch_assoc($vor2)) {
            $vornamen[] = $vor3['name'];
        }
        $nach1 = "SELECT name FROM " . CONFIG_TABLE_PREFIX . "namen_pool WHERE typ = 2";
        $nach2 = DB::query($nach1, false);
        $nach2a = mysql_num_rows($nach2) - 1;
        $nachnamen = array();
        while ($nach3 = mysql_fetch_assoc($nach2)) {
            $nachnamen[] = $nach3['name'];
        }

        for ($i = 0; $i < 100; $i++) {
            $talent = $this->getRandomStrength(3.0, 9.9);
            $anfangsstaerke = $this->getRandomStrength(0.5, 0.9);
            $staerke = round(($talent * $anfangsstaerke), 1);
            $this->createPlayer($vornamen[mt_rand(0, $vor2a)], $nachnamen[mt_rand(0, $nach2a)], $staerke, $talent, $pos, mt_rand(6205, 7665), 700000);
        }

        echo 'true';
        return;
    }

    private function createPlayer($vor, $nach, $staerke, $talent, $pos, $wiealt, $gehalt) {
        $sql = "INSERT INTO " . CONFIG_TABLE_PREFIX . "spieler (vorname, nachname, staerke, talent, position, wiealt, liga, team, gehalt, vertrag, spiele_verein, jugendTeam) VALUES ('" . $vor . "', '" . $nach . "', " . $staerke . ", " . $talent . ", '" . $pos . "', " . $wiealt . ", 'frei', 'frei', " . $gehalt . ", 0, 0, '')";
        $result = DB::query($sql, false);
        $insertedId = mysql_insert_id();
        $idMd5 = md5($insertedId);
        $idsSql = "UPDATE " . CONFIG_TABLE_PREFIX . "spieler SET ids = '" . $idMd5 . "' WHERE id = '" . $insertedId . "'";
        DB::query($idsSql, false);
        //echo $sql;
    }

    private function getRandomStrength($min, $max) {
        $ln_low = log($min, M_E);
        $logarithmicity = 1.15; // je höher desto weniger linear (am besten 0.5 < x < 1.5)
        $ln_high = log($max, M_E);
        $scale = $ln_high - $ln_low;
        $rand = pow(mt_rand() / mt_getrandmax(), $logarithmicity) * $scale + $ln_low;
        return round(pow(M_E, $rand), 1);
    }

}
