<?php

require_once '/../utils/database.php';
require_once '/../utils/utils.php';
require_once '/teamdata.php';

class Simulation {

    private $isSimulationTest;
    private $gameId;
    private $gamereport;
    private $team1;
    private $team2;
    private $isDerby;
    private $ballbesitz_team1;
    private $ballbesitz_team2;
    private $type;
    private $liga;

    public function __construct($gameId, $liga, $team1Name, $team2Name, $type, $recognition, $isSimulationTest) {
        $this->gameId = $gameId;
        $this->isSimulationTest = $isSimulationTest;
        $this->type = $type;
        $this->liga = $liga;
        $this->gamereport = new Gamereport($this->isSimulationTest);
        $this->team1 = new Teamdata($team1Name, $type, $this->gamereport);
        $this->team2 = new Teamdata($team2Name, $type, $this->gamereport);
        $this->setDerby($team1Name, $team2Name);
        $this->stadiondata = new Stadiondata($this->team1, $this->team2, $this->type, $liga, $this->gamereport);
        // Spiele nicht doppelt simulieren
        $this->setSimulated();
        $this->startSimulation();
        $this->updatePlayerValues();
        $this->selectScorersYellowRed();
    }

    private function setSimulated() {
        // Beim Testen keine Datenbankänderung
        if (!$this->isSimulationTest) {
            $sql = "UPDATE " . CONFIG_TABLE_PREFIX . "spiele SET simuliert = 1 WHERE id = " . $this->gameId . " AND simuliert = 0";
            DB::query($sql);
        }
    }

    private function setDerby($team1Name, $team2Name) {
        $kuerzelListe = array('AC', 'AJ', 'AS', 'ASC', 'ASV', 'Athletic', 'Atletico', 'Austria', 'AZ', 'BC', 'BSV', 'BV', 'Calcio', 'CD', 'CF', 'City', 'Club', 'Deportivo', 'Espanyol', 'FC', 'FF', 'FK', 'FSC', 'FSG', 'FV', 'IF', 'KV', 'Olympique', 'OSC', 'PSV', 'Racing', 'Rapid', 'Rapids', 'RC', 'RCD', 'Real', 'Rovers', 'RS', 'SG', 'SK', 'Spartans', 'Sporting', 'SSC', 'Sturm', 'SV', 'TSV', 'TV', 'UD', 'Union', 'United', 'Wanderers');
        $vereinsKurzelEntfernung = array();
        foreach ($kuerzelListe as $kuerzelEntry) {
            $vereinsKurzelEntfernung[] = $kuerzelEntry . ' ';
            $vereinsKurzelEntfernung[] = ' ' . $kuerzelEntry;
        }

        $team1OhneKurzel = str_replace($vereinsKurzelEntfernung, '', $team1Name);
        $team2OhneKurzel = str_replace($vereinsKurzelEntfernung, '', $team2Name);
        if ($team1OhneKurzel == $team2OhneKurzel) {
            $this->isDerby = TRUE;
            $this->team1->updateToDerby();
            $this->team2->updateToDerby();
        } else {
            $this->isDerby = FALSE;
        }
    }

    private function startSimulation() {
        $this->calcBallbesitz();
        $minutes = $this->get_minutes(20, $this->ballbesitz_team1);
        $tempmax = max($get_minuten);
        $spielzeit = $tempmax[0] + mt_rand(0, 3);
        $nachSpielZeit = $spielzeit - 90;

        foreach ($minutes as $minute_angreifer) {
            $minute = $minute_angreifer[0];
            if ($minute_angreifer[1] == 1) { // wenn Team 1 angreift
                $this->starte_angriff($this->team1, $this->team2);
            } else { // wenn Team 2 angreift
                $this->starte_angriff($this->team2, $this->team1);
            }
            if ($minute == 45) {
                $spielbericht .= '<p>45\': ' . kommentar('', 'mid_game');
            } elseif ($minute > 90) {
                $spielbericht .= '<p>90\': Der Assistent zeigt ' . $nachSpielZeit . ' Minuten Nachspielzeit an. Die Fans treiben ihr Team noch einmal richtig an.</p>';
            } elseif (Utils::Chance_Percent(1)) {
                if (Utils::Chance_Percent(50)) {
                    $spielbericht .= '<p>' . $minute . '\': Ein Flitzer belästigt den Stürmerstar von ' . $this->team1->teamName . '. Die anderen Spieler können den übermütigen Fan gerade noch bändigen!</p>';
                } else {
                    $spielbericht .= '<p>' . $minute . '\': ' . _('Was ist das denn? Ein Flitzer auf dem Spielfeld! Der Schiedsrichter unterbricht die Partie!') . '</p>';
                }
            }
        }

        if ($this->type == 'Pokal' OR $this->type == 'Cup') {
            $this->handleCupAndPokal(); // todo:. rewrite
        }

        $this->gamereport->appendCommentLine('<p>' . $spielzeit . '\': ' . _('Der Schiedsrichter pfeift das Spiel ab.') . '</p>');
        if ($this->team1->tore > $this->team2->tore) {
            $this->team1->teamWin();
        } elseif ($this->team2->tore > $this->team1->tore) {
            $this->team2->teamWin();
        }
    }

    private function calcBallbesitz() {
        $this->ballbesitz_team1 = round(100 / ($this->team2->staerke_team['M'] / $this->team1->staerke_team['M'] + 1));
        $this->ballbesitz_team2 = 100 - $this->ballbesitz_team1;
        if ($this->type != 'Test' && $this->liga != 'Pokal_Runde_5' && $this->type != 'Cup') { // kein Heimvorteil in Testspielen und Cupspielen und im Pokalfinale
            $this->ballbesitz_team1 += 4;
            $this->ballbesitz_team2 -= 4;
        }
        if ($this->ballbesitz_team1 > 100) {
            $this->ballbesitz_team1 = 100;
            $this->ballbesitz_team2 = 0;
        } elseif ($this->ballbesitz_team2 > 100) {
            $this->ballbesitz_team2 = 100;
            $this->ballbesitz_team1 = 0;
        }
    }

    private function get_minutes($anzahl_angriffe, $ballbesitz_team1) {
        $minuten_array = array();
        $intervall = 90 / $anzahl_angriffe;
        $spielraum = ceil($intervall) - 1;
        $minute = 1;
        $next = 0;
        $noch_angriffe_fuer_team1 = round($anzahl_angriffe * $ballbesitz_team1 / 100);
        for ($i = 1; $i <= $anzahl_angriffe; $i++) {
            // ENTSCHEIDEN WER ANGREIFT ANFANG
            $p_team1_greift_an = $noch_angriffe_fuer_team1 / ($anzahl_angriffe - $i + 1) * 100;
            $zufallszahl = mt_rand(0, 100);
            if ($zufallszahl < $p_team1_greift_an) {
                $angreifendes_team = 1;
            } else {
                $angreifendes_team = 2;
            }
            if ($angreifendes_team == 1) {
                $noch_angriffe_fuer_team1--;
            }
            // ENTSCHEIDEN WER ANGREIFT ENDE

            if ($next != 0) {
                $zufall_zeit = $next;
                $next = 0;
            } else {
                $zufall_zeit = mt_rand(-$spielraum, $spielraum);
                $next = -$zufall_zeit;
            }
            $minute = $minute + $intervall + $zufall_zeit;
            $minuten_array[] = array(round($minute), $angreifendes_team);
        }
        return $minuten_array;
    }

    private function starte_angriff($team_att, $team_def) {
        // input values: attacker's name, defender's name, attacker's strength array, defender's strength array
        // players' strength values vary from 0.1 to 9.9
        $this->gamereport->createGameComment($minute, $team_att->teamName);
        if (Utils::Chance_Percent(50 * $this->strengths_weight($team_att->staerke['M']) / $this->strengths_weight($team_def->staerke['M']) * $this->tactics_weight($team_att->tactics[0]) * $this->tactics_weight($team_def->tactics[0]) * tactics_weight($team_att->tactics[1]) / tactics_weight($team_att->tactics[2]))) {
            // attacking team passes 1st third of opponent's field side
            $this->gamereport->appentGameComment($team_def->teamName, 'advance');
            if (Utils::Chance_Percent(25 * $this->tactics_weight($team_def->tactics[5]))) {
                // the defending team fouls the attacking team
                $team_def->fouls++;
                $this->gamereport->appentGameComment($team_def->teamName, 'foul');
                if (Utils::Chance_Percent(30)) {
                    // yellow card for the defending team
                    $team_def->yellow_cards++;
                    $team_def->weakenTeam('yellow');
                    $this->gamereport->appentGameComment($team_def->teamName, 'yellow');
                } elseif (Utils::Chance_Percent(3)) {
                    // red card for the defending team
                    $team_def->red_cards++;
                    $team_def->weakenTeam('red');
                    $this->gamereport->appentGameComment($team_def->teamName, 'red');
                }
                // indirect free kick
                $this->gamereport->appentGameComment($team_att->teamName, 'iFreeKick');
                $team_att->schuesse++;
                if (Utils::Chance_Percent(30 * $this->strengths_weight($team_att->staerke['S']) / $this->strengths_weight($team_def->staerke['A']))) {
                    // shot at the goal
                    $this->gamereport->appentGameComment($team_att->teamName, 'iFreeKick_shot');
                    if (Utils::Chance_Percent(30 * $this->strengths_weight($team_att->staerke['S']) / $this->strengths_weight($team_def->staerke['T']))) {
                        // attacking team scores
                        $team_att->tore++;
                        $this->gamereport->appentGameComment($team_att->teamName, 'shot_score');
                    } else {
                        // defending goalkeeper saves
                        $this->gamereport->appentGameComment($team_def->teamName, 'iFreeKick_shot_save');
                    }
                } else {
                    // defending team cleares the ball
                    $this->gamereport->appentGameComment($team_def->teamName, 'iFreeKick_clear');
                }
            } elseif (Utils::Chance_Percent(17) * $this->tactics_weight($team_att->tactic[0]) * $this->tactics_weight($team_att->tactic[2])) {
                // attacking team is caught offside
                $team_att->abseits++;
                $this->gamereport->appentGameComment($team_att->teamName, 'offside');
            } else {
                // attack isn't interrupted
                // attack passes the 2nd third of the opponent's field side - good chance
                $this->gamereport->appentGameComment($team_def->teamName, 'advance_further');
                if (Utils::Chance_Percent(25 * $this->tactics_weight($team_def->tactic[5]))) {
                    // the defending team fouls the attacking team
                    $team_def->fouls++;
                    $this->gamereport->appentGameComment($team_def->teamName, 'foul');
                    if (Utils::Chance_Percent(33)) {
                        // yellow card for the defending team
                        $team_def->yellow_cards++;
                        $team_def->weakenTeam('yellow');
                        $this->gamereport->appentGameComment($team_def->teamName, 'yellow');
                    } elseif (Utils::Chance_Percent(3)) {
                        // red card for the defending team
                        $team_def->red_cards++;
                        $team_def->weakenTeam('red');
                        $this->gamereport->appentGameComment($team_def->teamName, 'red');
                    }
                    if (Utils::Chance_Percent(19 * $this->strengths_weight($team_att->staerke['S']) / $this->strengths_weight($team_def->staerke['A']))) {
                        // penalty for the attacking team
                        $team_att->schuesse++;
                        $this->gamereport->appentGameComment($team_att->teamName, 'penalty');
                        if (Utils::Chance_Percent(77) / $this->strengths_weight($team_def->staerke['T'])) {
                            // attacking team scores
                            $team_att->tore++;
                            $this->gamereport->appentGameComment($team_att->teamName, 'shot_score');
                        } elseif (Utils::Chance_Percent(50)) {
                            // shot misses the goal
                            $this->gamereport->appentGameComment($team_att->teamName, 'penalty_miss');
                        } else {
                            // defending goalkeeper saves
                            $this->gamereport->appentGameComment($team_def->teamName, 'penalty_save');
                        }
                    } else {
                        // direct free kick
                        $this->gamereport->appentGameComment($team_att->teamName, 'dFreeKick');
                        $team_att->schuesse++;
                        if (Utils::Chance_Percent(40 * $this->strengths_weight($team_att->staerke['S']))) {
                            // shot at the goal
                            $this->gamereport->appentGameComment($team_att->teamName, 'dFreeKick_shot');
                            if (Utils::Chance_Percent(40 / $this->strengths_weight($team_def->staerke['T']))) {
                                // attacking team scores
                                $team_att->tore++;
                                $this->gamereport->appentGameComment($team_att->teamName, 'shot_score');
                            } else {
                                // defending goalkeeper saves
                                $this->gamereport->appentGameComment($team_def->teamName, 'dFreeKick_shot_save');
                            }
                        } else {
                            // defending team cleares the ball
                            $this->gamereport->appentGameComment($team_def->teamName, 'dFreeKick_clear');
                        }
                    }
                } elseif (Utils::Chance_Percent(62 * $this->strengths_weight($team_att->staerke['S']) / $this->strengths_weight($team_def->staerke['A']) * $this->tactics_weight($team_att->tactic[2]) * $this->tactics_weight($team_att->tactic[3]))) {
                    // shot at the goal
                    $team_att->schuesse++;
                    $this->gamereport->appentGameComment($team_att->teamName, 'shot');
                    if (Utils::Chance_Percent(30 * $this->strengths_weight($team_att->staerke['S']) / $this->strengths_weight($team_def->staerke['T']))) {
                        // the attacking team scores
                        $Team_att->tore++;
                        $this->gamereport->appentGameComment($team_att->teamName, 'shot_score');
                    } else {
                        if (Utils::Chance_Percent(50 * $this->strengths_weight($team_def->staerke['A']))) {
                            // the defending defenders block the shot
                            $this->gamereport->appentGameComment($team_att->teamName, 'shot_block');
                        } else {
                            // the defending goalkeeper saves
                            $this->gamereport->appentGameComment($team_def->teamName, 'shot_save');
                        }
                    }
                } else {
                    // attack is stopped
                    $this->gamereport->appentGameComment($team_def->teamName, 'stopped');
                    if (Utils::Chance_Percent(15 * $this->strengths_weight($team_def->staerke['A']) / $this->strengths_weight($team_att->staerke['M']) * $this->tactics_weight($team_att->tactic[1]) * $this->tactics_weight($team_att->tactic[3]) * $this->tactics_weight($team_def->tactic[4]))) {
                        // quick counter attack - playing on the break
                        $team_att->starke['A'] *= 0.8; // weaken the current attacking team's defense
                        $this->gamereport->appentGameComment($team_def->teamName, 'quickCounterAttack');
                        $this->gamereport->closeGameComment($this->team1->tore, $this->team2->tore);
                        return $this->starte_angriff($team_def, $team_att); // new attack - this one is finished
                    }
                }
            }
        }
        // attacking team doesn't pass 1st third of opponent's field side
        elseif (Utils::Chance_Percent(15 * $this->strengths_weight($team_def->staerke['A']) / $this->strengths_weight($team_att->staerke['S']) * $this->tactics_weight($team_att->tactic[3]) * $this->tactics_weight($team_def->tactic[4]))) {
            // attack is stopped
            // quick counter attack - playing on the break
            $this->gamereport->appentGameComment($team_def->teamName, 'stopped');
            $team_att->staerke['A'] *= 0.8; // weaken the current attacking team's defense
            $this->gamereport->appentGameComment($team_def->teamName, 'quickCounterAttack');
            $this->gamereport->closeGameComment($this->team1->tore, $this->team2->tore);
            return $this->starte_angriff($team_def, $team_att); // new attack - this one is finished
        } else {
            // ball goes into touch - out of the field
            $this->gamereport->appentGameComment($team_def->teamName, 'throwIn');
            if (Utils::Chance_Percent(33)) {
                // if a new chance is created
                if (Utils::Chance_Percent(50 * $this->strengths_weight($team_att->staerke['M']) / $this->strengths_weight($team_def->staerke['M']))) {
                    // throw-in for the attacking team
                    $this->gamereport->appentGameComment($team_def->teamName, 'throwIn_att');
                    $this->gamereport->closeGameComment($this->team1->tore, $this->team2->tore); // close comment line
                    return $this->starte_angriff($team_att, $team_def); // new attack - this one is finished
                } else {
                    // throw-in for the defending team
                    $this->gamereport->appentGameComment($team_def->teamName, 'throwIn_def');
                    $this->gamereport->closeGameComment($this->team1->tore, $this->team2->tore); // close comment line
                    return $this->starte_angriff($team_def, $team_att); // new attack - this one is finished
                }
            }
        }
        $this->gamereport->closeGameComment($this->team1->tore, $this->team2->tore); // close comment line
        return TRUE; // finish the attack
    }

    private function tactics_weight($wert) {
        $neuerWert = $wert * 0.25 + 0.5;
        return $neuerWert;
    }

    private function strengths_weight($wert) {
        //$neuerWert = log10($wert+1)+0.35;
        $neuerWert = 0.125 * $wert + 0.0625;
        return $neuerWert;
    }

    private function handleCupAndPokal() {
        $kenn1 = "SELECT ergebnis FROM " . $prefix . "spiele WHERE kennung = '" . $sql3['kennung'] . "' LIMIT 0, 1"; // automatische Sortierung, Hinspiel wird zuerst gefunden
        $kenn2 = mysql_query($kenn1) or reportError(mysql_error(), $kenn1);
        if (mysql_num_rows($kenn2) == 0) { // auch im Cup wird 1 Spiel gefunden, naemlich das aktuelle, sonst Fehler
            $errRep1 = "UPDATE " . $prefix . "spiele SET simuliert = 0, simulationError = 'exit_kennung_not_found' WHERE id = " . $sql3['id'];
            $errRep2 = mysql_query($errRep1) or reportError(mysql_error(), $errRep1);
            exit;
        }
        $kenn3 = mysql_fetch_assoc($kenn2);
        $aktuelles_ergebnis = $tore[$sql3['team1']] . ':' . $tore[$sql3['team2']];
        if (($kenn3['ergebnis'] == $aktuelles_ergebnis) OR ( ($sql3['liga'] == 'Pokal_Runde_5' OR $sql3['typ'] == 'Cup') && $tore[$sql3['team1']] == $tore[$sql3['team2']])) { // wenn beide Spiele gleich enden wuerden
            $get_minuten = array();
            $get_minuten[] = array(mt_rand(96, 98), 1);
            $get_minuten[] = array(mt_rand(99, 101), 2);
            $get_minuten[] = array(mt_rand(102, 104), 1);
            $get_minuten[] = array(mt_rand(105, 107), 2);
            $get_minuten[] = array(mt_rand(108, 110), 1);
            $get_minuten[] = array(mt_rand(111, 113), 2);
            $get_minuten[] = array(mt_rand(114, 116), 1);
            $get_minuten[] = array(mt_rand(117, 119), 2);
            /* // TEAMS SCHWAECHEN FUER MEHR TORE ANFANG
              for ($wT = 0; $wT < 3; $wT++) {
              weakenTeam($sql3['team1'], $sql3['team1'], 'red');
              weakenTeam($sql3['team1'], $sql3['team2'], 'red');
              }
              // TEAMS SCHWAECHEN FUER MEHR TORE ENDE */
            foreach ($get_minuten as $minute_angreifer) {
                $minute = $minute_angreifer[0];
                if ($minute_angreifer[1] == 1) { // wenn Team 1 angreift
                    starte_angriff($sql3['team1'], $sql3['team2'], $staerke_team1, $staerke_team2);
                } else { // wenn Team 2 angreift
                    starte_angriff($sql3['team2'], $sql3['team1'], $staerke_team2, $staerke_team1);
                }
                $aktuelles_ergebnis = $tore[$sql3['team1']] . ':' . $tore[$sql3['team2']];
                if ($minute > 116 && (($kenn3['ergebnis'] == $aktuelles_ergebnis) OR ( ($sql3['liga'] == 'Pokal_Runde_5' OR $sql3['typ'] == 'Cup') && $tore[$sql3['team1']] == $tore[$sql3['team2']]))) { // wenn beide Spiele gleich enden wuerden
                    if (Chance_Percent(50)) {
                        $tore[$sql3['team2']] ++;
                        $spielbericht .= '<p>120\': ' . $sql3['team2'] . ' gewinnt im <strong>Elfmeterschießen!</strong> [' . $tore[$sql3['team1']] . ':' . $tore[$sql3['team2']] . ']</p>';
                    } else {
                        $tore[$sql3['team1']] ++;
                        $spielbericht .= '<p>120\': ' . $sql3['team1'] . ' gewinnt im <strong>Elfmeterschießen!</strong> [' . $tore[$sql3['team1']] . ':' . $tore[$sql3['team2']] . ']</p>';
                    }
                }
            }
            $erschoepungswert1 = $erschoepungswert1 + 1;
            $erschoepungswert2 = $erschoepungswert2 + 1;
            $spielzeit = 120;
        }
    }

    private function updatePlayerValues() {
        if (!$this->isSimulationTest) {
            $this->team1->updatePlayerValues();
            $this->team2->updatePlayerValues();
        }
    }

    private function selectScorersYellowRed() {
        if(!$this->isSimulationTest) {
            $this->team1->selectScorersYellowRed();
        }
    }

}

?>