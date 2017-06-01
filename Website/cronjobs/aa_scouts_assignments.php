<?php 
if (!isset($_GET['mode'])) { 
	include_once(__DIR__.'/../common/zzserver.php'); 
}

require_once(__DIR__.'/../utils/database.php');
require_once(__DIR__.'/../controller/messageController.php');

$date = mktime(date("H"), date("i"), date("s"), date("m"), date("d")-1, date("Y"));
$date = date('Y-m-d H:i:s', $date);
$sql = "SELECT a.team_ids, a.level, b.ids, b.vorname, b.nachname, b.talent, b.staerke FROM ".CONFIG_TABLE_PREFIX."scouts AS a JOIN ".CONFIG_TABLE_PREFIX."spieler AS b ON a.assignment_player_ids = b.ids WHERE last_assignment < '".$date."' AND assignment_player_ids IS NOT NULL";
$result = DB::query($sql, FALSE);

while ($entry = mysql_fetch_object($result)) {
    $schaetzung = schaetzungVomScout($entry->team_ids, $entry->level, $entry->ids, $entry->talent, $entry->staerke);
    $title = 'Scoutergebnis';
    $msg = 'Dein Scout ist zurück und glaubt das der Spieler '.getPlayerLink($entry->ids, $entry->vorname, $entry->nachname);

    if($schaetzung == $entry->talent) {
        $msg = $msg.' seinen Höhepunkt bereits erreicht hat.';
    } else {
        $msg = $msg.' eine Stärke von '.number_format($schaetzung, 1, ',', '.').' erreichen kann.';
    }

    $userSql = "SELECT ids FROM ".CONFIG_TABLE_PREFIX."users WHERE team = '".$entry->team_ids."'";
    $user = mysql_fetch_object(DB::query($userSql, FALSE));

    MessageController::addOfficialPn($user->ids, $title, $msg);

    $sql = "UPDATE ".CONFIG_TABLE_PREFIX."scouts SET assignment_player_ids = NULL WHERE team_ids = '".$entry->team_ids."'";
    DB::query($sql, FALSE);
}


function getPlayerLink($ids, $firstname, $lastname) {
    return '<a href="/spieler.php?id='.$ids.'">'.$firstname.' '.$lastname.'</a>';
}
?>