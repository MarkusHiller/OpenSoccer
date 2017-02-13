<?php
include_once(__DIR__.'/zz1.php');
require_once(__DIR__.'/utils/database.php');
require_once(__DIR__.'/controller/teamController.php');
require_once(__DIR__.'/controller/ligaController.php');
include_once(__DIR__.'/zz2.php');
?>
<title><?php echo _('Team Monitoring'); ?> - <?php echo CONFIG_SITE_NAME; ?></title>

<?php
if ($loggedin == 1) {
    if ($_SESSION['status'] != 'Admin') {
        exit;
    }
    echo '<script src="//cdnjs.cloudflare.com/ajax/libs/dygraph/1.1.1/dygraph-combined.js"></script>';
    $monitoredTeams = array(
        "8e98d81f8217304975ccb23337bb5761",
        "b137fdd1f79d56c7edf3365fea7520f2",
        "a8c88a0055f636e4a163a5e3d16adab7",
        "5b8add2a5d98b1a652ea7fd72d942dac",
        "e4bb4c5173c2ce17fd8fcd40041c068f",
        "c3c59e5f8b3e9753913f4d435b53c308",
        "758874998f5bd0c393da094e1967a72b",
        "24681928425f5a9133504de568f5f6df"
    );

    foreach ($monitoredTeams as $teamIds) {
        echo '<h1>' . TeamController::getTeamNameByIds($teamIds) . " - " . LigaController::getLigaNameByTeamIds($teamIds) . '</h1>';
        echo '<div id="'.$teamIds.'"></div><div id="'.$teamIds.'Status"></div>';
        $dataSql = "SELECT * FROM ". CONFIG_TABLE_PREFIX."computer_monitoring WHERE team = '".$teamIds."' ORDER BY date";
        $result = DB::query($dataSql, false);
        $data = 'Date, Anzahl Spieler T, Anzahl Spieler A, Anzahl Spieler M, Anzahl Spieler S, Avg Talent, Avg Staerke, Avg Aufstellung\n';
        while($line = mysql_fetch_object($result)) {
            $data .= $line->date.','.$line->anz_player_t.','.$line->anz_player_a.','.$line->anz_player_m.','.$line->anz_player_s.','.$line->avg_talent.','.$line->avg_staerke.','.$line->avg_aufstellung.'\n';
        }
        echo '<script type="text/javascript">new Dygraph(document.getElementById("'.$teamIds.'"),"'.$data.'", {legend: "always", labelsDiv: document.getElementById("'.$teamIds.'Status")});</script>';
    }
} else {

    echo '<p>' . _('Du musst angemeldet sein, um diese Seite aufrufen zu können!') . '</p>';
}
?>
<?php include_once(__DIR__.'/zz3.php'); ?>
