<?php
include_once(__DIR__ . '/zz1.php');
require_once(__DIR__ . '/utils/database.php');
include_once(__DIR__ . '/zz2.php');
?>
<title><?php echo _('Spielerverwaltung'); ?> - <?php echo CONFIG_SITE_NAME; ?></title>
<?php
if ($loggedin == 1) {
    if ($_SESSION['status'] != 'Admin') {
        exit;
    }
    //Gesamtanzahl aller Spieler in der Datenbank
    $transferPlayerSql = "SELECT COUNT(*) FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE transfermarkt = 1";
    $transferPlayerResult = DB::query($transferPlayerSql, false);
    $transferPlayers = mysql_result($transferPlayerResult, 0);
    
    //Freie Spieler -> nicht auf dem Transfermarkt und keinem Team zugeordnet nicht aelter als 28 Jahre
    $freePlayerSql = "SELECT COUNT(*) FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE team = 'frei' AND wiealt < 10585 AND transfermarkt = 0";
    $freePlayerResult = DB::query($freePlayerSql, false);
    $freePlayers = mysql_result($freePlayerResult, 0);
    
    //Freie T-Spieler -> nicht auf dem Transfermarkt und keinem Team zugeordnet nicht aelter als 28 Jahre
    $freePlayerTSql = "SELECT COUNT(*) FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE team = 'frei' AND position = 'T' AND wiealt < 10585 AND transfermarkt = 0";
    $freePlayerTResult = DB::query($freePlayerTSql, false);
    $freePlayersT = mysql_result($freePlayerTResult, 0);
    
    //Freie A-Spieler -> nicht auf dem Transfermarkt und keinem Team zugeordnet nicht aelter als 28 Jahre
    $freePlayerASql = "SELECT COUNT(*) FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE team = 'frei' AND position = 'A' AND wiealt < 10585 AND transfermarkt = 0";
    $freePlayerAResult = DB::query($freePlayerASql, false);
    $freePlayersA = mysql_result($freePlayerAResult, 0);
    
    //Freie M-Spieler -> nicht auf dem Transfermarkt und keinem Team zugeordnet nicht aelter als 28 Jahre
    $freePlayerMSql = "SELECT COUNT(*) FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE team = 'frei' AND position = 'M' AND wiealt < 10585 AND transfermarkt = 0";
    $freePlayerMResult = DB::query($freePlayerMSql, false);
    $freePlayersM = mysql_result($freePlayerMResult, 0);
    
    //Freie S-Spieler -> nicht auf dem Transfermarkt und keinem Team zugeordnet nicht aelter als 28 Jahre
    $freePlayerSSql = "SELECT COUNT(*) FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE team = 'frei' AND position = 'S' AND wiealt < 10585 AND transfermarkt = 0";
    $freePlayerSResult = DB::query($freePlayerSSql, false);
    $freePlayersS = mysql_result($freePlayerSResult, 0);

    include_once(__DIR__ . '/views/admin/playerManagement.html');
    include_once(__DIR__ . '/viewModels/admin/playerManagement.php');
} else {
    echo '<p>' . _('Du musst angemeldet sein, um diese Seite aufrufen zu können!') . '</p>';
}
?>
<?php include_once(__DIR__ . '/zz3.php'); ?>
