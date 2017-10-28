<?php 
include_once(__DIR__.'/../../common/zz1.php'); 
require_once(__DIR__.'/../../controller/emblemController.php');
$emblemResult = '';
if(count($_FILES) == 1) {
    $emblemResult = EmblemController::saveEmblemForTeamIds($cookie_team);
}

if(isset($_POST['selectedLiga']) && $_POST['selectedLiga'] != '') {
    echo $_POST['selectedLiga'];
    $selectedTeam = $_POST['selectedLiga'];
    $sql = "SELECT ids FROM " . CONFIG_TABLE_PREFIX . "teams WHERE liga = '$selectedTeam' AND ids NOT IN (SELECT team FROM " . CONFIG_TABLE_PREFIX . "users) ORDER BY RAND() LIMIT 1";
    $result = DB::query($sql, FALSE);
    $newTeam = mysql_result($result, 0);

    $preventNameDuplicates1 = "UPDATE " . CONFIG_TABLE_PREFIX . "teams SET name = CONCAT(name,'TEMP') WHERE ids = '$cookie_team' OR ids = '$newTeam'";
    DB::query($preventNameDuplicates1, false);
    $felderToChange = array('name', 'origName', 'liga', 'rank', 'punkte', 'tore', 'gegentore', 'vorjahr_platz', 'vorjahr_liga', 'pokalrunde', 'cuprunde', 'vorjahr_pokalrunde', 'vorjahr_cuprunde', 'sunS', 'sunU', 'sunN', 'elo');
    $felderWhichAreStrings = array('name', 'origName', 'liga', 'vorjahr_liga');
    $felderSelect = '';
    foreach ($felderToChange as $feldToChange) {
        $felderSelect .= $feldToChange.', ';
    }
    $felderSelect = substr($felderSelect, 0, -2);
    $daten1 = "SELECT ".$felderSelect." FROM " . CONFIG_TABLE_PREFIX . "teams WHERE ids = '$cookie_team' LIMIT 0, 1";
    $daten1 = DB::query($daten1, false);
    $daten2 = "SELECT ".$felderSelect." FROM " . CONFIG_TABLE_PREFIX . "teams WHERE ids = '$newTeam' LIMIT 0, 1";
    $daten2 = DB::query($daten2, false);
    if (mysql_num_rows($daten1) == 1 && mysql_num_rows($daten2) == 1) {
        $daten1 = mysql_fetch_assoc($daten1);
        $daten2 = mysql_fetch_assoc($daten2);
        $felderChangeSql = '';
        foreach ($felderToChange as $feldToChange) {
            $felderChangeSql .= $feldToChange." = ";
            if (in_array($feldToChange, $felderWhichAreStrings)) { $felderChangeSql .= "'"; }
            if ($feldToChange == 'name') {
                $felderChangeSql .= mysql_real_escape_string(substr($daten2[$feldToChange], 0, -4));
            }
            else {
                $felderChangeSql .= mysql_real_escape_string($daten2[$feldToChange]);
            }
            if (in_array($feldToChange, $felderWhichAreStrings)) { $felderChangeSql .= "'"; }
            $felderChangeSql .= ", ";
        }
        $felderChangeSql = substr($felderChangeSql, 0, -2);
        $sql1 = "UPDATE " . CONFIG_TABLE_PREFIX . "teams SET ".$felderChangeSql." WHERE ids = '$cookie_team'";
        $sql2 = DB::query($sql1, false);
        $sql1 = "UPDATE " . CONFIG_TABLE_PREFIX . "users SET liga = '" . mysql_real_escape_string($daten2['liga']) . "' WHERE ids = '$cookie_id'";
        $sql2 = DB::query($sql1, false);
        $felderChangeSql = '';
        foreach ($felderToChange as $feldToChange) {
            $felderChangeSql .= $feldToChange." = ";
            if (in_array($feldToChange, $felderWhichAreStrings)) { $felderChangeSql .= "'"; }
            if ($feldToChange == 'name') {
                $felderChangeSql .= mysql_real_escape_string(substr($daten1[$feldToChange], 0, -4));
            }
            else {
                $felderChangeSql .= mysql_real_escape_string($daten1[$feldToChange]);
            }
            if (in_array($feldToChange, $felderWhichAreStrings)) { $felderChangeSql .= "'"; }
            $felderChangeSql .= ", ";
        }
        $felderChangeSql = substr($felderChangeSql, 0, -2);
        $sql1 = "UPDATE " . CONFIG_TABLE_PREFIX . "teams SET ".$felderChangeSql." WHERE ids = '$newTeam'";
        $sql2 = DB::query($sql1, false);
        $tspiel1 = "DELETE FROM " . CONFIG_TABLE_PREFIX . "testspiel_anfragen WHERE team1 = '$cookie_team'";
        $tspiel2 = DB::query($tspiel1, false); // weil da falscher Name noch gespeichert ist
        $tspiel1 = "DELETE FROM " . CONFIG_TABLE_PREFIX . "transfermarkt_leihe WHERE (bieter = '".$daten1['name']."') AND akzeptiert = 0";
        $tspiel2 = DB::query($tspiel1, false); // weil da falscher Name noch gespeichert ist
        $nameChange1 = "UPDATE " . CONFIG_TABLE_PREFIX . "vnamechanges SET sperre = 0 WHERE team = '$cookie_team'";
        DB::query($nameChange1, false);
        // TAUSCH DURCHFUEHREN ENDE
        $sql1 = "INSERT INTO " . CONFIG_TABLE_PREFIX . "ligachanges (user1, team1, user2, team2, zeit, newLiga1, newLiga2) VALUES ('$cookie_id', '".$cookie_team."', '', '".$newTeam."', ".time().", '".mysql_real_escape_string($daten2['liga'])."', '".mysql_real_escape_string($daten1['liga'])."')";
        $sql2 = DB::query($sql1, false);
        
        //Logout thecurrent user
        session_destroy();
        unset($_SESSION['loggedin']);
        unset($_SESSION['userid']);
        unset($_SESSION['username']);
        unset($_SESSION['liga']);
        unset($_SESSION['team']);
        unset($_SESSION['teamname']);
		unset($_SESSION['anzeigen_wo']);
		unset($_SESSION['transferGesperrt']);

        header('Location: /club/settings.php?ligachange=true', true, 303);
        die();
    }
    header('Location: /club/settings.php?ligachange=false', true, 303);
    die();
    
}

$ligachangeResult = '';
if(isset($_GET['ligachange'])) {
    $ligachangeResult = $_GET['ligachange'];
}

echo '<title>'._('Einstellungen') . ' - ' . CONFIG_SITE_NAME . '</title>';
include_once(__DIR__.'/../../common/zz2.php'); 

$sperre1 = "SELECT MAX(zeit) FROM " . CONFIG_TABLE_PREFIX . "ligachanges WHERE team1 = '$cookie_team'";
$sperre2 = mysql_query($sperre1);
$sperre3 = mysql_result($sperre2, 0);
$daysToWait = 45-round((time()-$sperre3)/86400);

$sql = "SELECT ids, name FROM " . CONFIG_TABLE_PREFIX . "ligen WHERE ids != '$cookie_liga' ORDER BY name";
$result = DB::query($sql, FALSE);
$data = array();
while($row = mysql_fetch_array($result)) {
    $data['ligen'][] = $row;
}

$formSubmit = noDemoClick($cookie_id, TRUE);
$data['emblem'] = EmblemController::getEmblemByTeamIds($cookie_team);
$data['loggedin'] = $loggedin;
$data['emblemResult'] = $emblemResult;
$data['matchDay'] = GameTime::getMatchDay();
$data['daysToWait'] = $daysToWait;
$data['liveScoringType'] = $live_scoring_spieltyp_laeuft;
$data['ligachangeResult'] = $ligachangeResult;
$data['formSubmit'] = $formSubmit != "" ? $formSubmit : "confirm('Bist Du sicher?');";
echo $core->get(__DIR__.'/../../templates/club/settings.tpl', $data);

include_once(__DIR__.'/../../common/zz3.php'); ?>