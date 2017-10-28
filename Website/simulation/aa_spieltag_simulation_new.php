<?php

if (!isset($_GET['mode'])) {
    include_once(__DIR__.'/../common/zzserver.php');
}
set_time_limit(0);

require_once(__DIR__.'/simulation.php');

$isSimulationTest = false;
if(isset($_GET['testsimulation'])) {
    $isSimulationTest = true;
}

$heute_tag = date('d');
$heute_monat = date('m');
$heute_jahr = date('Y');
$heute_stunde = intval(date('H'));
$datum_min = mktime(00, 00, 01, $heute_monat, $heute_tag, $heute_jahr);
$datum_max = mktime(23, 59, 59, $heute_monat, $heute_tag, $heute_jahr);
if ($heute_stunde == 22 OR $heute_stunde == 23) {
    $to_simulate = 'Test';
} elseif ($heute_stunde == 14 OR $heute_stunde == 15) {
    $to_simulate = 'Liga';
} elseif ($heute_stunde == 18 OR $heute_stunde == 19) {
    $to_simulate = 'Pokal';
} elseif ($heute_stunde == 10 OR $heute_stunde == 11) {
    $to_simulate = 'Cup';
} else {
    exit;
}

$sql = "SELECT id, liga, team1, team2, typ, kennung FROM " . CONFIG_TABLE_PREFIX . "spiele WHERE datum > " . $datum_min . " AND datum < " . $datum_max . " AND simuliert = 0 AND ergebnis = '-:-' AND typ = '" . $to_simulate . "' ORDER BY RAND() LIMIT 0, 20";
$result = DB::query($sql, false) or reportError(mysql_error(), $sql);
while ($row = mysql_fetch_assoc($result)) {
    new Simulation($row['id'], $row['liga'], $row['team1'], $row['team2'], $row['typ'], $row['kennung'], $isSimulationTest);
}

?>