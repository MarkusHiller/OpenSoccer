<?php

if (!isset($_GET['mode'])) {
    include_once(__DIR__.'/zzserver.php');
}
require_once(__DIR__.'/utils/database.php');
require_once(__DIR__.'/utils/utils.php');

$in14days = Utils::endOfDay(Utils::getTimestamp('+14 days'));

//Goalkeeper fill
$sql = "SELECT * FROM " . CONFIG_TABLE_PREFIX . "teams WHERE (SELECT COUNT(*) FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE " . CONFIG_TABLE_PREFIX . "spieler.team = " . CONFIG_TABLE_PREFIX . "teams.ids AND position = 'T') < 3 AND ids NOT IN (SELECT team FROM " . CONFIG_TABLE_PREFIX . "users)";
$result = DB::query($sql, false);

$fill = 0;

while ($team = mysql_fetch_object($result)) {
    $countSql = "SELECT COUNT(*) FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE team = '" . $team->ids . "' AND position = 'T'";
    $countResult = DB::query($countSql, false);
    $limit = 3 - mysql_result($countResult, 0);
    $updateSql = "UPDATE " . CONFIG_TABLE_PREFIX . "spieler SET team = '" . $team->ids . "', liga = '" . $team->liga . "', vertrag = " . $in14days . ", frische = " . mt_rand(50, 100) . ", startelf_Liga = 0, startelf_Pokal = 0, startelf_Cup = 0, startelf_Test = 0, gehalt = ROUND(marktwert/14) WHERE team = 'frei' AND wiealt < 10585 AND transfermarkt = 0 AND position = 'T' ORDER BY RAND() LIMIT " . $limit;
    DB::query($updateSql, false);
    $fill += $limit;
}
echo "Goalkeeper: " . $fill;

//Defence fill
$sql = "SELECT * FROM " . CONFIG_TABLE_PREFIX . "teams WHERE (SELECT COUNT(*) FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE " . CONFIG_TABLE_PREFIX . "spieler.team = " . CONFIG_TABLE_PREFIX . "teams.ids AND position = 'A') < 7 AND ids NOT IN (SELECT team FROM " . CONFIG_TABLE_PREFIX . "users)";
$result = DB::query($sql, false);

$fill = 0;

while ($team = mysql_fetch_object($result)) {
    $countSql = "SELECT COUNT(*) FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE team = '" . $team->ids . "' AND position = 'A'";
    $countResult = DB::query($countSql, false);
    $limit = 7 - mysql_result($countResult, 0);
    $updateSql = "UPDATE " . CONFIG_TABLE_PREFIX . "spieler SET team = '" . $team->ids . "', liga = '" . $team->liga . "', vertrag = " . $in14days . ", frische = " . mt_rand(50, 100) . ", startelf_Liga = 0, startelf_Pokal = 0, startelf_Cup = 0, startelf_Test = 0, gehalt = ROUND(marktwert/14) WHERE team = 'frei' AND wiealt < 10585 AND transfermarkt = 0 AND position = 'A' ORDER BY RAND() LIMIT " . $limit;
    DB::query($updateSql, false);
    $fill += $limit;
}
echo "Defence: " . $fill;

//Midfeld fill
$sql = "SELECT * FROM " . CONFIG_TABLE_PREFIX . "teams WHERE (SELECT COUNT(*) FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE " . CONFIG_TABLE_PREFIX . "spieler.team = " . CONFIG_TABLE_PREFIX . "teams.ids AND position = 'M') < 7 AND ids NOT IN (SELECT team FROM " . CONFIG_TABLE_PREFIX . "users)";
$result = DB::query($sql, false);

$fill = 0;

while ($team = mysql_fetch_object($result)) {
    $countSql = "SELECT COUNT(*) FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE team = '" . $team->ids . "' AND position = 'M'";
    $countResult = DB::query($countSql, false);
    $limit = 7 - mysql_result($countResult, 0);
    $updateSql = "UPDATE " . CONFIG_TABLE_PREFIX . "spieler SET team = '" . $team->ids . "', liga = '" . $team->liga . "', vertrag = " . $in14days . ", frische = " . mt_rand(50, 100) . ", startelf_Liga = 0, startelf_Pokal = 0, startelf_Cup = 0, startelf_Test = 0, gehalt = ROUND(marktwert/14) WHERE team = 'frei' AND wiealt < 10585 AND transfermarkt = 0 AND position = 'M' ORDER BY RAND() LIMIT " . $limit;
    DB::query($updateSql, false);
    $fill += $limit;
}
echo "Midfeld: " . $fill;

//Striker fill
$sql = "SELECT * FROM " . CONFIG_TABLE_PREFIX . "teams WHERE (SELECT COUNT(*) FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE " . CONFIG_TABLE_PREFIX . "spieler.team = " . CONFIG_TABLE_PREFIX . "teams.ids AND position = 'S') < 4 AND ids NOT IN (SELECT team FROM " . CONFIG_TABLE_PREFIX . "users)";
$result = DB::query($sql, false);

$fill = 0;

while ($team = mysql_fetch_object($result)) {
    $countSql = "SELECT COUNT(*) FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE team = '" . $team->ids . "' AND position = 'S'";
    $countResult = DB::query($countSql, false);
    $limit = 4 - mysql_result($countResult, 0);
    $updateSql = "UPDATE " . CONFIG_TABLE_PREFIX . "spieler SET team = '" . $team->ids . "', liga = '" . $team->liga . "', vertrag = " . $in14days . ", frische = " . mt_rand(50, 100) . ", startelf_Liga = 0, startelf_Pokal = 0, startelf_Cup = 0, startelf_Test = 0, gehalt = ROUND(marktwert/14) WHERE team = 'frei' AND wiealt < 10585 AND transfermarkt = 0 AND position = 'S' ORDER BY RAND() LIMIT " . $limit;
    DB::query($updateSql, false);
    $fill += $limit;
}
echo "Striker: " . $fill;
?>

