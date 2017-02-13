<?php

require_once(__DIR__ . '/Logger/Log.php');
if (!isset($_GET['mode'])) {
    include_once(__DIR__ . '/zzserver.php');
}

function getRandomStrength($min, $max) {
    $ln_low = log($min, M_E);
    $logarithmicity = 1.15; // je höher desto weniger linear (am besten 0.5 < x < 1.5)
    $ln_high = log($max, M_E);
    $scale = $ln_high - $ln_low;
    $rand = pow(mt_rand() / mt_getrandmax(), $logarithmicity) * $scale + $ln_low;
    return round(pow(M_E, $rand), 1);
}

function choosePosition() {
    if (Chance_Percent(12)) {
        return 'T';
    } elseif (Chance_Percent(24)) {
        return 'S';
    } elseif (Chance_Percent(50)) {
        return 'M';
    } else {
        return 'A';
    }
}

function getTalentByScout($scout) {
    switch ($scout) {
        case 5:
            if (Chance_Percent(5)) {
                return getRandomStrength(9.5, 9.9);
            } else {
                return getRandomStrength(8.0, 9.7);
            }
            break;
        case 4:
            if (Chance_Percent(5)) {
                return getRandomStrength(8.5, 8.9);
            } else {
                return getRandomStrength(7, 8.7);
            }
            break;
        case 3:
            if (Chance_Percent(5)) {
                return getRandomStrength(7.5, 7.9);
            } else {
                return getRandomStrength(6, 7.7);
            }
            break;
        case 2:
            if (Chance_Percent(5)) {
                return getRandomStrength(6.5, 6.9);
            } else {
                return getRandomStrength(5, 6.7);
            }
            break;
        default:
            if (Chance_Percent(5)) {
                return getRandomStrength(5.5, 5.9);
            } else {
                return getRandomStrength(4, 5.7);
            }
            break;
    }
}

// KONFIGURATION ANFANG
$in_33_tagen = endOfDay(getTimestamp('+33 days'));
$spieltage_mit_aktion = array(3, 6, 9, 12, 15, 18, 21);
if (!in_array(GameTime::getMatchDay(), $spieltage_mit_aktion)) {
    exit;
}
$datum_stamp = mktime(0, 0, 0, date('m', time()), date('d', time()), date('Y', time()));
$datum_stamp_alt = getTimestamp('-36 hours', $datum_stamp); // vor 1,5 Tagen
$vor1 = "SELECT name FROM " . CONFIG_TABLE_PREFIX . "namen_pool WHERE typ = 1";
$vor2 = mysql_query($vor1);
$vor2a = mysql_num_rows($vor2) - 1;
$vornamen = array();
while ($vor3 = mysql_fetch_assoc($vor2)) {
    $vornamen[] = $vor3['name'];
}
$nach1 = "SELECT name FROM " . CONFIG_TABLE_PREFIX . "namen_pool WHERE typ = 2";
$nach2 = mysql_query($nach1);
$nach2a = mysql_num_rows($nach2) - 1;
$nachnamen = array();
while ($nach3 = mysql_fetch_assoc($nach2)) {
    $nachnamen[] = $nach3['name'];
}
// KONFIGURATION ENDE
$sql1 = "SELECT ids, liga, jugendarbeit, posToSearch FROM " . CONFIG_TABLE_PREFIX . "teams WHERE letzte_jugend < " . $datum_stamp_alt . " LIMIT 0, 100";
$sql2 = mysql_query($sql1);
while ($sql3 = mysql_fetch_assoc($sql2)) {
    $new_liga = $sql3['liga'];
    $new_team = $sql3['ids'];
    $vor_zahl = mt_rand(0, $vor2a); // Vorname - Position im Array
    $nach_zahl = mt_rand(0, $nach2a); // Nachname - Position im Array
    // MAXIMALES TALENT UND GEHALT ANFANG
    switch ($sql3['jugendarbeit']) {
        case 1: //$talent_max = 5.9;
            //$talent_min = 2.1;
            $new_gehalt = 300000;
            break;
        case 2: //$talent_max = 6.9;
            //$talent_min = 2.8;
            $new_gehalt = 500000;
            break;
        case 3: //$talent_max = 7.9;
            //$talent_min = 3.5;
            $new_gehalt = 700000;
            break;
        case 4: //$talent_max = 8.9;
            //$talent_min = 4.2;
            $new_gehalt = 900000;
            break;
        case 5: //$talent_max = 9.9;
            //$talent_min = 4.9;
            $new_gehalt = 1200000;
            break;
        default: //$talent_max = 5.9;
            //$talent_min = 2.1;
            $new_gehalt = 700000;
            break;
    }
    // MAXIMALES TALENT UND GEHALT ENDE
    $temp = $sql3['jugendarbeit'] - 1;
    // ERMITTELN VON STAERKE UND TALENT ANFANG
    $talent = getTalentByScout($sql3['jugendarbeit']); //getRandomStrength($talent_min, $talent_max);
    $anfangsstaerke = getRandomStrength(0.6, 0.9);
    $staerke = round(($talent * $anfangsstaerke), 1);
    // ERMITTELN VON STAERKE UND TALENT ENDE
    $ageInDays = mt_rand(6205, 7665); // Alter in Tagen
    $surname = $nachnamen[$nach_zahl];
    $firstname = $vornamen[$vor_zahl];
    $rein1 = "INSERT INTO " . CONFIG_TABLE_PREFIX . "spieler (vorname, nachname, staerke, talent, position, wiealt, liga, team, gehalt, vertrag, spiele_verein, jugendTeam) VALUES ('" . $firstname . "', '" . $surname . "', " . $staerke . ", " . $talent . ", '" . $sql3['posToSearch'] . "', " . $ageInDays . ", '" . $new_liga . "', '" . $new_team . "', " . $new_gehalt . ", " . $in_33_tagen . ", 0, '" . $new_team . "')";
    $rein2 = mysql_query($rein1);
    $rein3 = mysql_insert_id();
    $ids = md5($rein3);
    $rein4 = "UPDATE " . CONFIG_TABLE_PREFIX . "spieler SET ids = '" . $ids . "' WHERE id = '" . $rein3 . "'";
    $rein5 = mysql_query($rein4);

    //Logging
    $logMsg = 'Scout: ' . $sql3['jugendarbeit'] . ' - ' . number_format($staerke, 1) . ' (' . number_format($talent, 1) . ') - Age: ' . floor($ageInDays / 365) . ' - ID: ' . $ids . ' - Name: ' . $surname . ', ' . $firstname;
    Log::logToFile('player_creation', $logMsg);

    // PROTOKOLL ANFANG
    $getmanager4 = $firstname . ' ' . $surname;
    $formulierung = 'Der Jugendspieler <a href="/spieler.php?id=' . $ids . '">' . $getmanager4 . '</a> scheint großes Potenzial zu haben. Er hat deshalb einen Profivertrag für Deine erste Mannschaft bekommen.';
    $sql7 = "INSERT INTO " . CONFIG_TABLE_PREFIX . "protokoll (team, text, typ, zeit) VALUES ('" . $new_team . "', '" . $formulierung . "', 'Spieler', '" . time() . "')";
    $sql8 = mysql_query($sql7);
    // PROTOKOLL ENDE
    $ld1 = "UPDATE " . CONFIG_TABLE_PREFIX . "teams SET letzte_jugend = " . $datum_stamp . " WHERE ids = '" . $new_team . "'";
    $ld2 = mysql_query($ld1);
}
?>