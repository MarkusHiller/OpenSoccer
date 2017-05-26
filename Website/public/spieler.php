<?php include_once(__DIR__.'/../common/zz1.php');

if (!isset($_GET['id'])) { 
	exit; 
}

$sql1 = "SELECT id, ids, vorname, nachname, vertrag, position, wiealt, moral, staerke, talent, frische, marktwert, verhandlungsbasis, gehalt, transfermarkt, team, leiher, spiele_verein, spiele, spiele_saison, tore, verletzung, jugendTeam, pokalNurFuer FROM ".CONFIG_TABLE_PREFIX."spieler WHERE ".CONFIG_TABLE_PREFIX."spieler.ids = '".mysql_real_escape_string($_GET['id'])."'";
$sql2 = mysql_query($sql1);
$sql2a = mysql_num_rows($sql2);
if ($sql2a == 0) { 
	exit; 
}
$sql3 = mysql_fetch_assoc($sql2);

$tm1 = "SELECT name FROM ".CONFIG_TABLE_PREFIX."teams WHERE ids = '".$sql3['team']."'";
$tm2 = mysql_query($tm1);
$tm3 = mysql_fetch_assoc($tm2);
if ($loggedin == 1 && $cookie_team != '__'.$cookie_id) {
	$getkonto1 = "SELECT konto FROM ".CONFIG_TABLE_PREFIX."teams WHERE ids = '".$cookie_team."'";
	$getkonto2 = mysql_query($getkonto1);
	$getkonto3 = mysql_fetch_assoc($getkonto2);
	$getkonto4 = $getkonto3['konto'];
}
else {
	$getkonto4 = 0;
}
// TRANSFERSTATUS KORRIGIEREN ANFANG
$tsk1 = "SELECT COUNT(*) FROM ".CONFIG_TABLE_PREFIX."transfermarkt WHERE spieler = '".$sql3['ids']."'";
$tsk2 = mysql_query($tsk1);
$tsk3 = mysql_result($tsk2, 0);
if ($tsk3 == 0) { $tskwert = 0; }
else { $tskwert = 1; }
$tsk4 = "UPDATE ".CONFIG_TABLE_PREFIX."spieler SET transfermarkt = ".$tskwert." WHERE ids = '".$sql3['ids']."' AND transfermarkt < 999998";
$tsk5 = mysql_query($tsk4);
if ($sql3['transfermarkt'] < 999998) { // nur beim Verkauf
	$sql3['transfermarkt'] = $tskwert;
}
// TRANSFERSTATUS KORRIGIEREN ENDE
if ($loggedin == 1) {
    $watch1 = "SELECT COUNT(*) FROM ".CONFIG_TABLE_PREFIX."transfermarkt_watch WHERE team = '".$cookie_team."' AND spieler_id = '".$sql3['ids']."'";
    $watch2 = mysql_query($watch1);
    $watch3 = mysql_result($watch2, 0);
}
else {
    $watch3 = 0;
}
?>
<title><?php echo __('Spieler: %1$s %2$s', $sql3['vorname'], $sql3['nachname']); ?> - <?php echo CONFIG_SITE_NAME; ?></title>

<?php include_once(__DIR__.'/../common/zz2.php');

//In Template auslagern anfang
if (isset($_GET['action'])) {
	if ($_GET['action'] == 'setWatching') {
		setTaskDone('watch_player');
	}
}
if (isset($_GET['sellSuccess'])) {
	$sellPrice = number_format($_GET['sellSuccess'], 0, ',', '.');
	addInfoBox(__('Du hast den Spieler erfolgreich für %s € verkauft.', $sellPrice));
}
$schaetzungVomScout = schaetzungVomScout($cookie_team, $cookie_scout, $_GET['id'], $sql3['talent'], $sql3['staerke'], $sql3['team']);

if ($sql3['team'] == 'frei') {
	$sql3['frische'] = getRegularFreshness(GameTime::getMatchDay());
}

if ($loggedin == 1 && $sql3['team'] == $cookie_team) {
	$gehaltContent = number_format($sql3['gehalt'], 0, ',', '.').' €';
} elseif ($sql3['team'] != 'frei') {
	$sosi = 0;
	if ($sql3['gehalt'] > 99999999) { $sosi = 8; }
	elseif ($sql3['gehalt'] > 9999999) { $sosi = 7; }
	elseif ($sql3['gehalt'] > 999999) { $sosi = 6; }
	elseif ($sql3['gehalt'] > 99999) { $sosi = 5; }
	elseif ($sql3['gehalt'] > 9999) { $sosi = 4; }
	elseif ($sql3['gehalt'] > 999) { $sosi = 3; }
	elseif ($sql3['gehalt'] > 99) { $sosi = 2; }
	$sidfhisudhfuo = round($sql3['gehalt']/pow(10, $sosi))*pow(10, $sosi);
	$gehaltContent = 'ca. '.number_format($sidfhisudhfuo, 0, ',', '.').' €';
}

if ($sql3['team'] == 'frei') {
	$transferStat = 'unbekannt';
} else {
	if ($sql3['transfermarkt'] == 0) {
		if ($sql3['leiher'] != 'keiner') {
			$transferStat = 'Ausgeliehen';
		} else {
			$transferStat = 'Unverkäuflich';
		}
	} elseif ($sql3['transfermarkt'] == 1) {
		$transferStat = '<a href="/transfermarkt_auktion.php?id='.$sql3['ids'].'">Zur Auktion</a>';
	} elseif ($sql3['transfermarkt'] > 999998) {
		$getLeihPos1 = "SELECT COUNT(*) FROM ".CONFIG_TABLE_PREFIX."spieler WHERE transfermarkt > 999998 AND ((staerke > ".$sql3['staerke'].") OR (staerke = ".$sql3['staerke']." AND id < ".$sql3['id']."))";
		$getLeihPos2 = mysql_query($getLeihPos1);
		$getLeihPos3 = mysql_result($getLeihPos2, 0);
		$getLeihPage = floor($getLeihPos3/$eintraege_pro_seite)+1;
		$transferStat = '<a href="/transfermarkt_leihe.php?seite='.$getLeihPage.'&amp;mark='.$sql3['ids'].'">Zur Leihgabe</a>';
	}
}

if ($sql3['jugendTeam'] == $sql3['team'] && $sql3['gehalt'] % 100000 == 0) {
	$entlassungskosten = 0;
} else {
	$entlassungskosten = $sql3['gehalt']*ceil(($sql3['vertrag']-time())/86400/22)/2;
}

$onWatchClick = noDemoClick($cookie_id, TRUE);
$talentStars = round($schaetzungVomScout/9.9*5);
$data = $sql3;
$data['loggedin'] = $loggedin;
$data['cookie_id'] = $cookie_id;
$data['cookie_team'] = $cookie_team;
$data['transferGesperrt'] = $_SESSION['transferGesperrt'];
$data['onWatchClick'] = $onWatchClick != "" ? $onWatchClick : "confirm('Bist Du sicher?');";
$data['alter'] = floor($sql3['wiealt']/365);
$data['teamname'] = $tm3['name'];
$data['live_scoring_spieltyp_laeuft'] = $live_scoring_spieltyp_laeuft;
$data['talentStars'] = $talentStars;
$data['schaetzungVomScout'] = $schaetzungVomScout;
$data['entlassungskosten'] = $entlassungskosten;
$data['gehaltContent'] = $gehaltContent;
$data['transferStat'] = $transferStat;
echo $core->get(__DIR__.'/../templates/spieler.tpl', $data);

include_once(__DIR__.'/../common/zz3.php'); 
?>
