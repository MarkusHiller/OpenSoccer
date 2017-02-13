<?php include_once(__DIR__ . '/zz1.php'); ?>
<title><?php echo _('Aufstellung'); ?> - <?php echo CONFIG_SITE_NAME; ?></title>
<style type="text/css">
    .os-player-row-injured td, .os-player-row-injured td a { color: #ff0000; }
</style>
<?php
include_once(__DIR__ . '/zz2.php');
if ($_SESSION['via_android'] == 1) {
    include_once(__DIR__ . '/aufstellungOld.php');
    return;
}
?>
<h1><?php echo _('Aufstellung'); ?></h1>
<?php
$spieltypAufstellung = 'Liga';

if ($loggedin == 1) {
    include_once(__DIR__ . '/views/aufstellung.html');

    $gf1 = "SELECT spieler, farbe FROM " . $prefix . "spieler_mark WHERE team = '" . $cookie_team . "'";
    $gf2 = mysql_query($gf1);
    $mark = array();
    $markierungen = array();
    while ($gf3 = mysql_fetch_assoc($gf2)) {
        $mark[] = $gf3;
        $markierungen[$gf3['spieler']] = $gf3['farbe'];
    }

    if (isset($_GET['orderBy'])) {
        switch ($_GET['orderBy']) {
            case 'AL':
                $orderBy = "wiealt";
                break;
            case 'MO':
                $orderBy = "moral";
                break;
            case 'FR':
                $orderBy = "frische DESC";
                break;
            default:
                $orderBy = "staerke DESC";
        }
    } else {
        $orderBy = "staerke DESC";
    }

    $sql1 = "SELECT ids, position, vorname, nachname, wiealt, moral, staerke, talent, frische, startelf_" . $spieltypAufstellung . " AS startelfWert, verletzung, startelf_Liga, startelf_Pokal, startelf_Cup, startelf_Test FROM " . $prefix . "spieler WHERE team = '" . $cookie_team . "' ORDER BY position = 'S', position = 'M', position = 'A', position = 'T', " . $orderBy;
    $sql2 = mysql_query($sql1);
    $counter = 0;
    $players = array();
    while ($sql3 = mysql_fetch_assoc($sql2)) {
        $sql3['talent'] = number_format(schaetzungVomScout($cookie_team, $cookie_scout, $sql3['ids'], $sql3['talent'], $sql3['staerke']), 1, ',', '.');
        $players[] = $sql3;
    }

    include_once(__DIR__ . '/viewModels/aufstellung.php');
} else {
    echo '<p>' . _('Du musst angemeldet sein, um diese Seite aufrufen zu können!') . '</p>';
}
include_once(__DIR__ . '/zz3.php');
?>
