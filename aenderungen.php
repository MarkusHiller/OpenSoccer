<?php include 'zz1.php'; ?>
<title><?php echo _('Änderungen'); ?> - <?php echo CONFIG_SITE_NAME; ?></title>
<?php include 'zz2.php'; ?>
<?php if ($loggedin == 1) { ?>
<h1><?php echo _('Änderungen durchsuchen'); ?></h1>
<form action="/aenderungen.php" method="get" accept-charset="utf-8">
<p><input type="text" name="q" style="width:200px" /> <input type="submit" value="<?php echo _('Suchen'); ?>" /></p>
</form>
<?php
setTaskDone('open_changes');
if (isset($_GET['q'])) { $q = mysql_real_escape_string(trim(strip_tags($_GET['q']))); } else { $q = ''; }
if ($q == '') {
	echo '<h1>'._('Änderungen').'</h1>';
}
else {
	echo '<h1>'._('Änderungen zum Thema').' &quot;'.$q.'&quot;</h1>';
}
?>
<p><strong><?php echo _('Hier findest Du alle <i>Änderungen</i> - gesammelt auf einer Seite.'); ?></strong></p>
<?php
$changesList = file('aenderungen.php.txt');
$counter = -1;
foreach ($changesList as $changesEntry) {
    // increment the counter
    $counter++;
    // ignore the first line (PHP tag)
    if ($counter == 0) { continue; }
    // be careful with the input for eval() here (which should only contain a gettext call)
    $changesEntry = eval($changesEntry);
	if ($q != '') {
		if (strpos($changesEntry, $q) === FALSE) {
			continue;
		}
	}
	echo '<p><b>'.sprintf('%03s', $counter).'.</b> '.$changesEntry.'</p>';
}
?>
<?php } else { ?>
<h1><?php echo _('Änderungen'); ?></h1>
<p><?php echo _('Du musst angemeldet sein, um diese Seite aufrufen zu können!'); ?></p>
<?php } ?>
<?php include 'zz3.php'; ?>
