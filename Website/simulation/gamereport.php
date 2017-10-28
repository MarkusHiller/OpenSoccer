<?php

class Gamereport {

    private $gamereport = '';
    private $isSimulationTest;
    private $gameComments = array();

    public function __construct($isSimulationTest) {
        $this->isSimulationTest = $isSimulationTest;
    }

    public function addStadionPart($stadionName, $watcher, $price) {
        if (stripos($stadionName, 'Arena') !== FALSE) {
            $stadionPreposition = 'in der';
        } else {
            $stadionPreposition = 'im';
        }
        if ($watcher4 >= 90000) {
            $this->gamereport .= '<p>Knapp 100.000 Zuschauer hier ' . $stadionPreposition . ' ' . $stadionName . '!</p>';
        } elseif ($watcher4 >= 70000) {
            $this->gamereport .= '<p>Eine super Stimmung ' . $stadionPreposition . ' ' . $stadionName . '!</p>';
        } elseif ($watcher4 >= 50000) {
            $this->gamereport .= '<p>Gute Stimmung hier ' . $stadionPreposition . ' ' . $stadionName . '!</p>';
        } elseif ($watcher4 >= 30000) {
            $this->gamereport .= '<p>Die Fans freuen sich auf das Spiel ' . $stadionPreposition . ' ' . $stadionName . '!</p>';
        } else {
            $this->gamereport .= '<p>Es sind nicht viele Zuschauer hier ' . $stadionPreposition . ' ' . $stadionName . '!</p>';
        }
        $this->gamereport .= '<p>' . _('Ticketpreis:') . ' ' . $price . ' €</p>';
    }

    public function addNomination($teamName, $players) {
        $this->gamereport .= '<p>Aufstellung von ' . $teamName . ': ';
        foreach ($players as $player) {
            if ($player->name == "Amateurspieler") {
                $this->gamereport .= 'Amateurspieler (' . $player->position . '/' . number_format($player->staerke, 1, ',', '.') . '), ';
            } else {
                $this->gamereport .= '<a href="/spieler.php?id=' . $player->ids . '">' . $this->getPlayername($player) . '</a>, ';
            }
        }
        $this->gamereport .= substr($this->gamereport, 0, -2);
    }

    public function addSponsorStrafe($value) {
        $this->gamereport .= ' [Sponsor-Strafe: ' . $value . ' €]';
    }

    public function closeTeamdata() {
        $this->gamereport .= '</p>';
    }

    public function createFormLine() {
        //TODO:: implement after finished convert
        // Gamereport has to store all pars in separate variabels and after call Build build the parts together
    }

    public function createGameComment($minute, $teamname) {
        $this->gameComments[] = '<p>'.$minute.'\': '.$this->kommentar($teamname, 'attack');
    }

    public function appentGameComment($replace, $type) {
        end($this->gameComments) .= ' ' . kommentar($replace, $type);
    }

    public function closeGameComment($toreTeam1, $toreTeam2) {
        end($this->gameComments) .= ' [' . $toreTeam1 . ':' . $toreTeam2 . ']</p>';
    }
    
    public function appendCommentLine($line) {
        $this->gameComments[] = $line;
    }

    private function getPlayername($player) {
        return mb_substr($player->vorname, 0, 1, 'UTF-8') . '. ' . $player->nachname;
    }

    private function kommentar($ersetzung, $typ) {
        $formulierungen = array();
        $formulierungen['start_game'] = array(
            _('Anstoß!'),
            _('Das Publikum ist gut gelaunt beim Anpfiff des heutigen Spiels.'),
            _('Das Spiel beginnt.'),
            _('Das Spiel fängt mit dem Anstoß vor begeisterten Zuschauern an.'),
            _('Bei windigem und kaltem Wetter beginnt das Spiel.'),
            _('Dunkle Wolken bildeten sich, als die Spieler das Feld betraten. Anstoß.'),
            _('Die Sonne scheint, das Gras ist grün und das Publikum freut sich aufs Spiel.'),
            _('Das Spielfeld ist etwas nass nach einigen Tagen Regen. Die erste Halbzeit beginnt.'),
            _('Los geht\'s mit der ersten Halbzeit!')
        );
        $formulierungen['mid_game'] = array(
            _('Halbzeit!'),
            _('Der Wind nimmt zu, die zweite Halbzeit beginnt.'),
            _('Los geht\'s mit der zweiten Halbzeit!'),
            _('Anpfiff zur zweiten Halbzeit.'),
            _('15 Minuten Pause.'),
            _('Die Spieler der Gäste sehen konzentrierter aus zu Beginn der zweiten Halbzeit.'),
            _('Der Schiedsrichter pfeift zur zweiten Halbzeit.'),
            _('Die Spieler des Gastgebers winken den Zuschauern zu, das Spiel geht weiter.'),
            _('Die Spieler des Gastgebers sehen frischer aus, währen die das Spielfeld zur zweiten Halbzeit betreten.')
        );
        $formulierungen['attack'] = array(
            _('XYZ am Ball.'),
            _('XYZ greift an.'),
            _('XYZ macht wieder Druck.'),
            _('Ballbesitz für XYZ.'),
            _('XYZ hat den Ball.')
        );
        $formulierungen['advance'] = array(
            _('Der Ballführende spielt den öffnenden Pass.'),
            _('Den Mitspieler mit einem öffnenden Pass bedient.'),
            _('Starker Diagonalpass.'),
            _('Pass zum Mitspieler.'),
            _('Schönes Dribbling im Mittelfeld.'),
            _('Pass von der rechten Seite nach innen.'),
            _('Pass von der linken Seite nach innen.'),
            _('Der Mitspieler steht völlig frei.'),
            _('Schöner Pass zum Mitspieler.'),
            _('Der präzise Pass kommt genau im richtigen Moment an.'),
            _('Der Ballführende spielt einen feinen Pass nach vorne.'),
            _('Sehenswertes Dribbling.'),
            _('Schöne Ballstafette in den eigenen Reihen.'),
            _('Schneller Doppelpass im Mittelfeld.'),
            _('Schönes Dribbling.'),
            _('Herrliche Kombination der Offensiv-Spieler.'),
            _('Der ungenaue Pass kommt mit Glück an.'),
            _('Unpräzises Anspiel, aber der Gegner kommt zu spät.'),
            _('Der Spieler nutzt seine Schnelligkeit.'),
            _('Guter Querpass zum Teamkollegen.')
        );
        $formulierungen['advance_further'] = array(
            _('Schöne Vorlage.'),
            _('Tolle Vorlage.'),
            _('Ein Dribbling wie aus dem Lehrbuch.'),
            _('Der Mitspieler dribbelt ein, zwei Spieler aus.'),
            _('Der Mitspieler dribbelt einen, nein zwei, Spieler aus.'),
            _('Der Mitspieler wird schön in Szene gesetzt.'),
            _('Der Angreifer lässt seine Gegenspieler stehen.'),
            _('Geschickt freigespielt.'),
            _('Schönes Dribbling des Stürmers.'),
            _('Der Stürmer wird herrlich von seinem Mitspieler bedient.'),
            _('Steilpass auf den linken Flügel.'),
            _('Der Spieler lässt seinen Gegner stehen.'),
            _('Der Spieler tunnelt seinen Gegner.'),
            _('Dribbling über den halben Platz.'),
            'Der Ballführende vernascht ' . mt_rand(2, 3) . ' Abwehrspieler.',
            _('Steilpass auf den rechten Flügel.'),
            _('Den Kollegen mustergültig freigespielt.'),
            _('Traumpass auf den linken Flügelspieler.'),
            _('Traumpass auf den rechten Flügelspieler.'),
            _('Traumpass in die Spitze.'),
            _('Ball selbstlos quergelegt.'),
            _('Der kreuzende Mitspieler wird geschickt.'),
            _('Der Angreifer wird halblinks geschickt.'),
            _('Der Angreifer wird halbrechts geschickt.'),
            _('Steilpass auf den Stürmer, der jetzt alleine aufs Tor zuläuft.'),
            _('Die Offensivabteilung arbeitet eine Chance heraus.'),
            _('Jetzt wirds gefährlich ...'),
            _('Zuspiel in die Spitze.'),
            _('Langer Pass auf den Stürmer.'),
            _('Flanke aus dem Halbfeld.'),
            _('Schöne Flanke von rechts.'),
            _('Tolle Flanke von links.'),
            _('Schöne Flanke von links.'),
            _('Tolle Flanke von rechts.'),
            _('Schöne Flanke.'),
            _('Gute Flanke.'),
            _('Der Flügelspieler flankt auf den Stürmer.'),
            _('Der Spieler legt perfekt für seinen Teamkollegen auf.'),
            _('Der Angreifer legt für seinen Mitspieler zurück.')
        );
        $formulierungen['yellow'] = array(
            '<span style="padding:2px;background-color:#ff0;">Gelbe Karte</span> für XYZ.',
            'Der Schiedsrichter zeigt <span style="padding:2px;background-color:#ff0;">Gelb</span>.',
            'Der Referee zückt die <span style="padding:2px;background-color:#ff0;">Gelbe Karte</span>.',
            'XYZ erhält eine <span style="padding:2px;background-color:#ff0;">Gelbe Karte</span>.',
            'XYZ kassiert eine <span style="padding:2px;background-color:#ff0;">Gelbe Karte</span>.',
            'Der Spieler von XYZ sieht <span style="padding:2px;background-color:#ff0;">Gelb</span>.',
            'Der Schiedsrichter zögert nicht lange: <span style="padding:2px;background-color:#ff0;">Gelb</span>!',
            'Der Schiedsrichter zögert nicht lange: <span style="padding:2px;background-color:#ff0;">Verwarnung</span>!',
            'Der Spieler von XYZ holt sich die <span style="padding:2px;background-color:#ff0;">Gelbe Karte</span> ab.',
            'Klare <span style="padding:2px;background-color:#ff0;">Gelbe Karte</span> für XYZ.'
        );
        $formulierungen['red'] = array(
            '<span style="padding:2px;background-color:#f00;color:#fff;">Platzverweis</span> für XYZ!',
            'Dem Schiedsrichter bleibt keine andere Wahl als hier die <span style="padding:2px;background-color:#f00;color:#fff;">Rote Karte</span> zu zeigen.',
            '<span style="padding:2px;background-color:#f00;color:#fff;">Rote Karte</span> für XYZ!',
            'Der Spieler von XYZ wird <span style="padding:2px;background-color:#f00;color:#fff;">des Feldes verwiesen</span>!',
            'Der Spieler von XYZ darf <span style="padding:2px;background-color:#f00;color:#fff;">vorzeitig duschen gehen</span>!',
            'Der Schiedsrichter <span style="padding:2px;background-color:#f00;color:#fff;">schickt den Übeltäter vom Platz</span>!',
            'Der Schiedsrichter zögert nicht lange: <span style="padding:2px;background-color:#f00;color:#fff;">Rot</span>!',
            'Der Schiedsrichter zögert nicht lange: <span style="padding:2px;background-color:#f00;color:#fff;">Platzverweis</span>!',
            'Der Schiedsrichter entscheidet auf Notbremse und zieht die <span style="padding:2px;background-color:#f00;color:#fff;">Rote Karte</span>!',
            'XYZ sieht nach einer Unsportlichkeit <span style="padding:2px;background-color:#f00;color:#fff;">Rot</span>!',
            'Der Unparteiische ahndet diese Aktion mit der <span style="padding:2px;background-color:#f00;color:#fff;">Roten Karte</span>!',
            'Der Spieler von XYZ kassiert <span style="padding:2px;background-color:#f00;color:#fff;">Rot</span>!',
            'Der Referee zückt die <span style="padding:2px;background-color:#f00;color:#fff;">Rote Karte</span>!',
            'Der Schiedsrichter zeigt <span style="padding:2px;background-color:#f00;color:#fff;">Rot</span>!'
        );
        $formulierungen['iFreeKick_shot_save'] = array(
            _('Der Torwart hat den Ball sicher.'),
            _('Der Torwart von XYZ hält.'),
            _('Der Ball fliegt Richtung Eckfahne.'),
            _('Leichte Aufgabe für den Schlussmann von XYZ.'),
            _('Kein Problem für den Torhüter.'),
            _('Der Keeper fängt den Ball locker ab.')
        );
        $formulierungen['dFreeKick_shot_save'] = array(
            _('Glanzparade vom Schlussmann!'),
            _('Was für ein Reflex!'),
            _('Souverän gehalten.')
        );
        $formulierungen['foul'] = array(
            _('Grobes Foul!'),
            _('Foul von XYZ.'),
            _('Bösartiges Foul des Abwehrspielers!'),
            _('Der Verteidiger setzt zur Grätsche an.'),
            _('Handspiel von XYZ.'),
            _('Diese Grätsche ging nur in die Beine des Gegners.'),
            _('Den Ball wollte er mit dieser Aktion sicher nicht holen. Grobes Foul!'),
            _('Was für eine Schwalbe! Hat der Schiedsrichter da etwa mehr gesehen als wir?'),
            _('War das wirklich ein Foul? Da kann man drüber streiten.'),
            _('Der Stürmer wird von den Beinen geholt.'),
            _('Foul vom Abwehrspieler.'),
            _('Der Spieler setzt sich mit dem Ellenbogen durch. Foul!'),
            _('Das hat der Referee gesehen - Foulspiel.'),
            _('Der Spieler wird zu Fall gebracht.'),
            _('Das sah nach einem Revanchefoul aus.'),
            _('Der Angreifer wird umgestoßen.')
        );
        $formulierungen['penalty'] = array(
            '<span style="text-transform:uppercase;">' . _('Der Schiedsrichter zeigt auf den Punkt.') . '</span>',
            '<span style="text-transform:uppercase;">' . _('Elfmeter!') . '</span>',
            '<span style="text-transform:uppercase;">' . _('Der Unparteiische entscheidet auf Elfmeter.') . '</span>',
            '<span style="text-transform:uppercase;">' . _('Strafstoß!') . '</span>',
            '<span style="text-transform:uppercase;">' . _('Strafstoß für XYZ!') . '</span>',
            '<span style="text-transform:uppercase;">' . _('Elfmeter für XYZ!') . '</span>'
        );
        $formulierungen['penalty_save'] = array(
            _('Der Torwart springt in die richtige Ecke ... und hält!'),
            _('Der Torwart bleibt stehen - Gehalten! Schwacher Schuss.'),
            _('Der Schlussmann von XYZ hält!'),
            _('Den hat der Keeper sicher!'),
            _('Schwach geschossen.'),
            _('Sensationelle Parade!'),
            _('Den hat der Keeper sicher. Schlecht geschossen!'),
            _('Toll gehalten vom Torwart!')
        );
        $formulierungen['penalty_miss'] = array(
            _('Drüber!'),
            _('XYZ schießt drüber!'),
            _('In die Wolken!'),
            _('An den Pfosten!'),
            _('XYZ schießt daneben!'),
            _('XYZ trifft nur den Pfosten.'),
            _('Schwacher Schuss vom Mittelfeldspieler!'),
            _('Der Stürmer setzt den Elfmeter an die Latte!'),
            _('Er vergibt die Chance!'),
            _('Das war knapp. Da fehlten nur Zentimeter.'),
            _('Der geht daneben!')
        );
        $formulierungen['iFreeKick'] = array(
            _('Indirekter Freistoß für XYZ!'),
            _('Indirekter Freistoß!'),
            _('Der Schiedsrichter entscheidet auf Freistoß, indirekt.'),
            'Freistoß für XYZ, ca. ' . mt_rand(19, 35) . 'm vor dem gegnerischen Tor.'
        );
        $formulierungen['dFreeKick'] = array(
            _('Direkter Freistoß für XYZ!'),
            _('Direkter Freistoß!'),
            _('Freistoß an der Strafraumgrenze.'),
            _('Der Schiedsrichter entscheidet auf Freistoß, direkt.'),
            _('Direkter Freistoß vor dem Strafraum. 6 Mann in der Mauer.'),
            'Freistoß für XYZ, ca. ' . mt_rand(17, 23) . 'm vor dem gegnerischen Tor.'
        );
        $formulierungen['iFreeKick_clear'] = array(
            _('Per Kopfball geklärt.'),
            _('XYZ klärt.'),
            _('Schuss geht daneben.'),
            _('Der Abwehrspieler klärt.'),
            _('Der Verteidiger bereinigt die Situation.'),
            _('Der Schuss geht auf die Tribüne. Hoffentlich hat sich kein Zuschauer verletzt.'),
            _('Der Schuss geht auf die Tribüne. Die Fans freuen sich über den gefangenen Ball.'),
            _('Der Schuss geht weit daneben.')
        );
        $formulierungen['stopped'] = array(
            _('Ballverlust im Mittelfeld.'),
            _('XYZ macht Druck und holt sich den Ball wieder.'),
            _('Perfektes Pressing, Ballverlust.'),
            _('XYZ steht zu gut.'),
            _('Keine Anspielstation.'),
            _('Unnötiger Fehlpass!'),
            _('Erstklassig verteidigt von XYZ.'),
            _('Schön verteidigt von XYZ.'),
            _('An diesem Verteidiger kommt heute wohl keiner vorbei!'),
            _('Der Gegner rutscht aus und XYZ holt sich den Ball wieder.'),
            _('Schwerer Ballverlust im Mittelfeld.'),
            _('Da ist kein Durchkommen.'),
            _('Die Verteidigung von XYZ steht sicher.'),
            _('XYZ stoppt den Angriff.'),
            _('Pass abgefangen.'),
            _('XYZ holt sich den Ball wieder.'),
            _('Fehlpass. Der Gegner hat den Ball.'),
            _('Der Gegner holt sich den Ball wieder.')
        );
        $formulierungen['iFreeKick_shot'] = array(
            _('Ein Spieler von XYZ kommt zum Kopfball.'),
            _('Per Kopf in den Strafraum verlängert.'),
            'Der Ball wird aus ' . mt_rand(25, 45) . 'm hoch in den Strafraum gebracht.',
            'Schuss aus ' . mt_rand(25, 45) . 'm, halbrechte Position.',
            'Schuss aus ' . mt_rand(25, 45) . 'm, halblinke Position.',
            _('Der Ball wird abgefälscht ...'),
            'Flatterball aus ' . mt_rand(25, 45) . 'm.',
            _('Der Ball wird verlängert.'),
            _('Zur Seite gelegt und Schuss!')
        );
        $formulierungen['dFreeKick_shot'] = array(
            _('Flachschuss.'),
            _('Da war noch jemand dran.'),
            _('Abgefälschter Schuss.'),
            _('Der Ball streift die Mauer.'),
            _('Schöne Freistoß-Variante.'),
            _('Der Ball geht durch die Mauer.'),
            _('Schön um die Mauer gezirkelt.')
        );
        $formulierungen['dFreeKick_clear'] = array(
            _('Der Ball geht in die Mauer.'),
            _('Der Schuss geht daneben.'),
            _('Der Schuss geht auf die Tribüne. Hoffentlich hat sich kein Zuschauer verletzt.'),
            _('Der Schuss geht auf die Tribüne. Die Fans freuen sich über den gefangenen Ball.'),
            _('Der Schuss geht weit drüber.'),
            _('Da fehlten nur Zentimeter.'),
            _('Der Verteidiger wirft sich in den Schuss.')
        );
        $formulierungen['shot'] = array(
            _('Schuss und ...'),
            _('Kopfball und ...'),
            'Der Stürmer zieht aus ' . mt_rand(18, 30) . 'm ab.',
            _('Der Angreifer schließt perfekt ins kurze Eck ab.'),
            _('Der Stürmer zirkelt den Ball ins lange Eck.'),
            'Der Spieler zieht ab aus ' . mt_rand(18, 30) . 'm.',
            _('XYZ setzt einen Schuss flach ins lange Eck.'),
            'Flatterball aus ' . mt_rand(18, 30) . 'm.',
            _('Der Stürmer kommt am langen Eck unbedrängt zum Kopfball.'),
            _('Direktschuss aus spitzem Winkel.'),
            _('Einfach mal aufs Tor geschossen, der Schlussmann hat damit nicht gerechnet.'),
            _('Schuss aus spitzem Winkel.'),
            _('Schuss aus dem Rückraum.'),
            _('Ein Versuch aus der Distanz!'),
            _('Der Stürmer versucht den Ball über den Torwart zu lupfen.'),
            _('Der Angreifer donnert den Ball mit einem Seitfallzieher aufs Tor.'),
            _('Jetzt muss er nur noch den Keeper überwinden ...'),
            _('Nur noch der Torwart steht vor dem Angreifer.'),
            _('Der Angreifer versucht, den Torwart mit einem Heber zu überwinden.'),
            _('Kaltschnäuziger Abschluss.'),
            _('Toller Weitschuss von XYZ.')
        );
        $formulierungen['shot_score'] = array(
            '<strong>' . _('Der passt genau!') . '</strong>',
            '<strong>' . _('Den hätte der Keeper haben müssen!') . '</strong>',
            '<strong>' . _('Der Ball donnert ins Netz!') . '</strong>',
            '<strong>' . _('Keine Chance für den Keeper, der Ball flatterte zu sehr!') . '</strong>',
            '<strong>' . _('Der Torwart springt ... und kommt nicht mehr dran. Tor!') . '</strong>',
            '<strong>' . _('XYZ netzt mit Glück ein. Der Torwart war dran.') . '</strong>',
            '<strong>' . _('XYZ trifft.') . '</strong>',
            '<strong>' . _('Tor des Tages!') . '</strong>',
            '<strong>' . _('Direkt unter die Querlatte. Tor!') . '</strong>',
            '<strong>' . _('XYZ netzt ein.') . '</strong>',
            '<strong>' . _('Tor!') . '</strong>',
            '<strong>' . _('Ein super Tor!') . '</strong>',
            '<strong>' . _('Gooolaso!') . '</strong>',
            '<strong>' . _('Der Ball kullert irgendwie ins Tor!') . '</strong>',
            '<strong>' . _('Der Ball geht ins Tor!') . '</strong>',
            '<strong>' . _('Abstauber ins Tor!') . '</strong>',
            '<strong>' . _('Nachschuss und ... Tor!') . '</strong>',
            '<strong>' . _('Keine Chance für den Keeper!') . '</strong>',
            '<strong>' . _('Treffer!') . '</strong>',
            '<strong>' . _('Toooor!') . '</strong>',
            '<strong>' . _('Der Schlussmann zeigt keine Reaktion und sieht den Ball im Netz zappeln!') . '</strong>',
            '<strong>' . _('Was für ein Treffer!') . '</strong>',
            '<strong>' . _('Sensationelles Tor!') . '</strong>',
            '<strong>' . _('Unglaublich! Toooor!') . '</strong>',
            '<strong>' . _('Tor für XYZ!') . '</strong>'
        );
        $formulierungen['shot_block'] = array(
            _('Der Schuss wird vom Verteidiger abgeblockt.'),
            _('Der Abwehrspieler kann noch an den Pfosten abfälschen.'),
            _('Der Stürmer verzieht leicht nach links.'),
            _('Das war knapp, der Schuss geht über die Latte.'),
            _('Der Schuss geht auf die Tribüne. Hoffentlich hat sich kein Zuschauer verletzt.'),
            _('Der Schuss geht auf die Tribüne. Die Fans freuen sich über den gefangenen Ball.'),
            _('XYZ verfehlt das Tor nur um Zentimeter.'),
            _('Der Ball knallt an den Pfosten. Ein Raunen geht durch das Stadion.'),
            _('Der Schuss geht weit daneben.'),
            _('Der Ball knallt ans Lattenkreuz.'),
            _('Der Stürmer kommt angerauscht und schlägt ein wunderbares Luftloch.'),
            _('Ball zur Ecke geklärt.')
        );
        $formulierungen['shot_save'] = array(
            _('Gehalten!'),
            _('Tolle Parade vom Torwart.'),
            _('Der Keeper wehrt den Ball mit den Fäusten ab.'),
            _('Der Torwart wehrt den Ball ab.'),
            _('Der Schlussmann kann den Ball mit einer herrlichen Parade retten.'),
            _('Der Torhüter fliegt ... und hält! Tolle Parade!'),
            _('Der Torwart klatscht nur ab, aber der Nachschuss geht vorbei.'),
            _('Der Schlussmann fängt sicher.'),
            _('Der Torhüter vereitelt die Chance mit einem tollen Reflex.'),
            _('Der Torwart bewahrt sein Team mit einer Glanzparade vor dem Gegentreffer.')
        );
        $formulierungen['offside'] = array(
            _('Abseits.'),
            _('XYZ im Abseits.'),
            _('Schönes Tor von XYZ, doch leider Abseits.'),
            _('Ein Spieler von XYZ läuft ins Abseits.'),
            _('Der Spieler von XYZ steht im Abseits.'),
            _('XYZ ist im Abseits.'),
            _('Der Spieler von XYZ wird in Abseitsposition angespielt.'),
            _('Der Schiedsrichter erkennt zu Recht auf Abseits.'),
            _('Der Referee entscheidet auf Abseitsstellung.'),
            _('Abseits! Die Spieler können es kaum glauben.'),
            _('Der Spieler steht im Abseits.')
        );
        $formulierungen['quickCounterAttack'] = array(
            _('XYZ kontert.'),
            _('Schneller Gegenangriff von XYZ.'),
            _('Sofort der Konter von XYZ.'),
            _('Konterchance für XYZ.'),
            _('Kontermöglichkeit für XYZ.'),
            _('XYZ hat sofort wieder die Kugel und geht in die Offensive.'),
            _('Schnelles Umschalten von XYZ.'),
            _('XYZ schaltet direkt in den Angriff um.'),
            _('Direkter Konter von XYZ.'),
            _('Schönes Konterspiel von XYZ.'),
            _('Schneller Konter von XYZ.')
        );
        $formulierungen['throwIn'] = array(
            _('Ball im Aus. Einwurf.'),
            _('Der Ball geht ins Aus.'),
            _('Die Kugel rollt ins Aus. Einwurf.'),
            _('Einwurf tief in der gegnerischen Hälfte.')
        );
        $formulierungen['throwIn_def'] = array(
            _('Der Gegner hat den Ball.'),
            _('Ballbesitz für den Gegner.')
        );
        $formulierungen['throwIn_att'] = array(
            _('XYZ hat den Ball.'),
            _('Ballbesitz für XYZ.')
        );
        if (isset($formulierungen[$typ])) {
            $formulierung = $formulierungen[$typ];
        } else {
            return $typ;
        }
        shuffle($formulierung);
        $ausgabe = str_replace('XYZ', $ersetzung, $formulierung[0]);
        return $ausgabe;
    }

}

?>