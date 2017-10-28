<?php

require_once '/../utils/database.php';

class Teamdata {

    private $teamIds;
    private $teamName;
    private $type;
    private $gamereport;
    private $players = array();
    private $verletzungenVorauswahl = array();
    private $frischeWerte = array();
    private $positionsCounter = array('T' => 1, 'A' => 4, 'M' => 4, 'S' => 2);
    private $scorers = array();
    private $staerkenArray = array();
    private $staerke;
    private $strafeWegenUnvollstaendigkeitTeam;
    private $elo;
    private $fanaufkommen;
    private $rank;
    public $tore = 0;
    private $sponsordata;
    public $tactic;
    private $erschoepfungswert;
    private $gesamtForm;
    public $fouls = 0;
    public $yellow_cards = 0;
    public $red_cards = 0;
    public $schuesse = 0;
    public $abseits = 0;
    private $tore_team;

    public function __construct($teamName, $type, $gamereport) {
        $this->teamName = $teamName;
        $this->type = $type;
        $this->gamereport = $gamereport;
        $this->init();
    }

    private function init() {
        $this->loadTeamdata();
        $this->loadTactic();
        $this->loadPlayers();
        $this->calcStats();
    }

    private function loadTeamData() {
        $sql = "SELECT ids, rank, fanaufkommen, sponsor_a, sponsor_s, elo FROM " . CONFIG_TABLE_PREFIX . "teams WHERE name = '" . mysql_real_escape_string(trim($this->teamName)) . "'";
        $result = DB::query($sql);
        $data = mysql_fetch_object($result);
        $this->teamIds = $data->ids;
        $this->rank = $data->rank;
        $this->fanaufkommen = $data->fanaufkommen;
        $this->elo = $data->elo;
        $this->sponsordata = new Sponsordata($data->sponsor_a, $data->sponsor_b);
    }

    private function loadTactic() {
        $sql = "SELECT ausrichtung, geschw_auf, pass_auf, risk_pass, druck, aggress FROM " . CONFIG_TABLE_PREFIX . "taktiken WHERE team = '" . $this->teamIds . "' AND spieltyp = '" . $this->type . "'";
        $result = DB::query($sql);
        if (mysql_num_rows($result) == 0) {
            $this->tactic = [2, 2, 2, 2, 2, 2];
        } else {
            $data = mysql_fetch_assoc($result);
            $this->tactic = array($data['ausrichtung'], $data['geschw_auf'], $data['pass_auf'], $data['risk_pass'], $data['druck'], $data['aggress']);
        }
    }

    private function loadPlayers() {
        $sql = "SELECT ids, vorname, nachname, position, frische, staerke FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE team = '" . $this->teamIds . "' AND startelf_" . $this->type . " != 0 AND verletzung = 0 ORDER BY position DESC LIMIT 0, 11";
        $result = DB::query($sql, false);
        $this->calcPlayerStats($result);
        $this->fillTeamWithAmateur();
        $this->gamereport->addNomination($this->teamName, $this->players);
    }

    private function calcPlayerStats($data) {
        while ($player = mysql_fetch_object($data)) {
            $this->verletzungenVorauswahl[$player->ids] = mt_rand(0, $player->frische);
            $this->frischeWerte[] = $player->frische;
            $this->players[] = $player;
            $this->positionsCounter[$player->position] --;
            switch ($player->position) {
                case 'T': continue 2;
                case 'A': $temp = round($player->staerke * 0.3);
                    break;
                case 'M': $temp = round($player->staerke * 1.8);
                    break;
                case 'S': $temp = round($player->staerke * 3.7);
                    break;
            }
            $temp = mt_rand($temp, 45);
            $this->scorers[$player->ids] = $temp;
            $this->staerkenArray[] = $player->staerke;
        }
    }

    private function fillTeamWithAmateur() {
        $strafeWegenUnvollstaendigkeitTeam = -2;
        if (count($this->staerkenArray) == 0 OR array_sum($this->staerkenArray) < 11) {
            $amateurStaerke = 1;
        } else {
            $amateurStaerke = array_sum($this->staerkenArray) / count($this->staerkenArray) / 2;
        }
        foreach (array_keys($this->positionsCounter) as $positionsBuchstabe) {
            for ($auffuellen = 0; $auffuellen < $this->positionsCounter[$positionsBuchstabe]; $auffuellen++) {
                $amateurStaerkeCurrent = mt_rand(80, 120) * $amateurStaerke / 100;
                $this->players[] = (object) ['name' => 'Amateurspieler', 'ids' => 'Amateurspieler', 'position' => $positionsBuchstabe, 'frische' => mt_rand(50, 100), 'staerke' => $amateurStaerkeCurrent];
                $strafeWegenUnvollstaendigkeitTeam++;
            }
        }
        if ($this->type != 'Test') {
            if ($strafeWegenUnvollstaendigkeitTeam > 0) {
                $this->strafeWegenUnvollstaendigkeitTeam = $strafeWegenUnvollstaendigkeitTeam * 1000000;
                $this->gamereport->addSponsorStrafe(number_format($strafeWegenUnvollstaendigkeitTeam1, 0, ',', '.'));
            }
        } else {
            $this->strafeWegenUnvollstaendigkeitTeam = 0;
        }
        $this->gamereport->closeTeamdata();
    }

    public function updateToDerby() {
        $this->tactic[5] += 1;
        $this->tactic[0] += 1;
        $this->fanaufkommen += 20000;
    }

    private function calcStats() {
        $this->erschoepfungswert = $this->tactic[4] + 1;
        if ($this->tactic[0] == 4) {
            $taktik1v = 70;
            $taktik1a = 125;
        } elseif ($this->tactic[0] == 3) {
            $taktik1v = 80;
            $taktik1a = 115;
        } elseif ($this->tactic[0] == 1) {
            $taktik1v = 115;
            $taktik1a = 80;
        } else {
            $taktik1v = 100;
            $taktik1a = 100;
        }
        $this->staerke = array('T' => 0.0, 'A' => 0.0, 'M' => 0.0, 'S' => 0.0);
        for ($i = 0; $i < 11; $i++) {
            if (isset($this->players[$i])) {
                $this->staerke[$this->players[$i]['position']] += $this->players[$i]['staerke'] * (0.33 + 0.67 * $this->players[$i]['frische'] / 100);
            } else { // keine 11 Spieler
                $this->staerke['T'] += 0.1;
                $this->staerke['A'] += 0.1;
                $this->staerke['M'] += 0.1;
                $this->staerke['S'] += 0.1;
            }
        }
        $this->gesamtForm = $this->staerke['T'] + $this->staerke['A'] + $this->staerke['M'] + $this->staerke['S'];
        $this->staerke['A'] = $this->staerke['A'] / 4;
        $this->staerke['M'] = $this->staerke['M'] / 4;
        $this->staerke['S'] = $this->staerke['S'] / 2;
        $this->staerke['A'] *= $taktik1v / 100;
        $this->staerke['S'] *= $taktik1a / 100;
    }

    public function weakenTeam($cardType) {
        if ($cardType == 'red') {
            $weakenFactor = 0.9;
        } elseif ($cardType == 'yellow') {
            $weakenFactor = 0.98;
        } else {
            $weakenFactor = 1;
        }

        $this->staerke['A'] *= $weakenFactor;
        $this->staerke['M'] *= $weakenFactor;
        $this->staerke['S'] *= $weakenFactor;
    }

    public function teamWin() {
        $this->sponsordata->teamWin();
    }

    public function updatePlayerValues() {
        $pflichtspielPlus = ", spiele = spiele+1, moral = moral+1.8";
        if ($this->type == 'Test') {
            $pflichtspielPlus = ", moral = moral+1";
        }
        $pokalNurFuerSQL = "";
        if ($this->type == 'Pokal') {
            $pokalNurFuerSQL = ", pokalNurFuer = team";
        }

        $sql = "UPDATE " . CONFIG_TABLE_PREFIX . "spieler SET frische = frische-" . $this->erschoepfungswert . $pflichtspielPlus . ", spiele_gesamt = spiele_gesamt+1, spiele_verein = spiele_verein+1" . $pokalNurFuerSQL . " WHERE team = '" . $this->teamIds . "' AND startelf_" . $this->type . " != 0 AND verletzung = 0 LIMIT 11";
        DB::query($sql, false);
    }

    public function selectScorersYellowRed() {
        if (isset($this->scorers)) {
            if (is_array($this->scorers)) {
                if (count($this->scorers) > 3) {
                    // TORE ANFANG
                    arsort($this->scorers);
                    for ($i = 0; $i < $this->tore; $i++) {
                        if ($scorer = each($this->scorers)) {
                            if ($this->type != 'Test') {
                                $sql = "UPDATE " . CONFIG_TABLE_PREFIX . "spieler SET tore = tore+1 WHERE ids = '" . $scorer['key'] . "'";
                                DB::query($sql, false);
                            }
                            $this->tore_team .= $scorer['key'] . '-';
                        }
                        if (mt_rand(0, 3) == 2) {
                            reset($this->scorers);
                        }
                    }
                    $this->tore_team = substr($this->tore_team, 0, -1);
                    reset($this->scorers);
                    // TORE ENDE
                    // VERLETZUNGEN ANFANG
                    if (count($this->frischeWerte) == 0) {
                        $frischeAvg = 0;
                    } else {
                        $frischeAvg = array_sum($this->frischeWerte) / count($this->frischeWerte);
                    }
                    if (GameTime::getMatchDay() < 3 || GameTime::getMatchDay() >= 22) {
                        $frischeAvg = 100;
                    } // an den ersten beiden Spieltagen und am letzten keine Verletzungen
                    $risikoFuerVerletzungTeam = floor((100 - $frischeAvg) * 1.35);
                    if (Utils::Chance_Percent($risikoFuerVerletzungTeam)) {
                        asort($this->verletzungenVorauswahl, SORT_NUMERIC);
                        if ($verletzter = each($this->verletzungenVorauswahl)) {
                            $verletzungsDaten = create_verletzung();
                            $sql = "UPDATE " . CONFIG_TABLE_PREFIX . "spieler SET verletzung = " . $verletzungsDaten['dauer'] . ", startelf_" . $this->type . " = 0 WHERE ids = '" . $verletzter['key'] . "'";
                            DB::query($sql, false);
                            $formulierung = '<a href="/spieler.php?id=' . $verletzter['key'] . '">Einer Deiner Spieler</a> fällt ' . $verletzungsDaten['name'] . ' für ' . $verletzungsDaten['dauer'] . ' Tage aus.';
                            $sql = "INSERT INTO " . CONFIG_TABLE_PREFIX . "protokoll (team, text, typ, zeit) VALUES ('" . $this->teamIds . "', '" . mysql_real_escape_string($formulierung) . "', 'Verletzung', " . time() . ")";
                            DB::query($sql, false);
                        }
                    }
                    // VERLETZUNGEN ENDE
                    // GELB ANFANG
                    asort($this->scorers);
                    for ($i = 0; $i < $gelbe_karten[$sql3['team1']]; $i++) {
                        if ($temp = each($scorers1)) {
                            //$torj1 = "UPDATE ".$prefix."spieler SET karten = karten+0.001 WHERE ids = '".$temp['key']."'";
                            //$torj2 = mysql_query($torj1) or reportError(mysql_error(), $torj1);
                            $gelb_team1 .= $temp['key'] . '-';
                            $scorers1[$temp['key']] *= 10; // damit der Scorer-Wert sehr hoch ist und der Spieler nicht noch mal gezogen wird
                        }
                    }
                    $gelb_team1 = substr($gelb_team1, 0, -1);
                    reset($scorers1);
                    // GELB ENDE
                    // ROT ANFANG
                    asort($scorers1);
                    for ($i = 0; $i < $rote_karten[$sql3['team1']]; $i++) {
                        if ($temp = each($scorers1)) {
                            //$torj1 = "UPDATE ".$prefix."spieler SET karten = karten+1 WHERE ids = '".$temp['key']."'";
                            //$torj2 = mysql_query($torj1) or reportError(mysql_error(), $torj1);
                            $rot_team1 .= $temp['key'] . '-';
                            $scorers1[$temp['key']] *= 10; // damit der Scorer-Wert sehr hoch ist und der Spieler nicht noch mal gezogen wird
                        }
                    }
                    $rot_team1 = substr($rot_team1, 0, -1);
                    // ROT ENDE
                }
            }
        }
    }

    private function create_verletzung() {
        if (mt_rand(10, 20) < 17) {
            $vName = _('wegen einer Muskelzerrung');
            $vDauer = 1;
        } elseif (mt_rand(10, 20) < 17) {
            $vName = _('aufgrund einer Verstauchung');
            $vDauer = 3;
        } elseif (mt_rand(10, 20) < 17) {
            $vName = _('wegen einer Prellung');
            $vDauer = 5;
        } elseif (mt_rand(10, 20) < 17) {
            $vName = _('aufgrund eines Muskelfaserrisses');
            $vDauer = 7;
        } elseif (mt_rand(10, 20) < 17) {
            $vName = _('wegen eines Bänderrisses');
            $vDauer = 9;
        } elseif (mt_rand(10, 20) < 17) {
            $vName = _('aufgrund eines Knorpelschadens');
            $vDauer = 11;
        } else {
            $vName = _('durch einen Knochenbruch');
            $vDauer = 13;
        }
        $daten = array('name' => $vName, 'dauer' => $vDauer);
        return $daten;
    }

}

?>