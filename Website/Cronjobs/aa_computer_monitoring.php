<?php

if (!isset($_GET['mode'])) {
    include_once(__DIR__.'/zzserver.php');
}
require_once(__DIR__.'/utils/database.php');

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

foreach ($monitoredTeams as $value) {
    $sqlAnzPlayer = "(SELECT COUNT(*) FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE team = '" . $value . "')";
    $sqlAnzPlayerT = "(SELECT COUNT(*) FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE team = '" . $value . "' AND position = 'T')";
    $sqlAnzPlayerS = "(SELECT COUNT(*) FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE team = '" . $value . "' AND position = 'S')";
    $sqlAnzPlayerM = "(SELECT COUNT(*) FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE team = '" . $value . "' AND position = 'M')";
    $sqlAnzPlayerA = "(SELECT COUNT(*) FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE team = '" . $value . "' AND position = 'A')";
    $sqlAvgTalent = "(SELECT AVG(talent) FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE team = '" . $value . "')";
    $sqlAvgStaerke = "(SELECT AVG(staerke) FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE team = '" . $value . "')";
    $sqlAvgAufstellung = "(SELECT AVG(staerke) FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE team = '" . $value . "' AND startelf_Liga = 1)";

    $sql = "INSERT INTO " . CONFIG_TABLE_PREFIX . "computer_monitoring VALUES ('" . date('Y/m/d') . "', '" . $value . "', " . $sqlAnzPlayer . ", " . $sqlAnzPlayerT . ", " . $sqlAnzPlayerS . ", " . $sqlAnzPlayerM . ", " . $sqlAnzPlayerA . ", " . $sqlAvgTalent . ", " . $sqlAvgStaerke . ", " . $sqlAvgAufstellung . ")";
    DB::query($sql, false);
}

?>

