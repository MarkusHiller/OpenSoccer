<?php 
include 'zz1.php'; 
require_once 'controller/emblemController.php';
$result = '';
if(count($_FILES) == 1) {
    $result = EmblemController::saveEmblemForTeamIds($cookie_team);
};
?>
<title><?php echo _('Einstellungen'); ?> - <?php echo CONFIG_SITE_NAME; ?></title>
<?php include 'zz2.php'; ?>
<h1><?php echo _('Einstellungen'); ?></h1>
<?php if ($loggedin == 1) { ?>
<p><?php echo _('Hier kannst du vereinsspeziefische Einstellungen vornehmen.'); ?></p>

<h1><?php echo _('Wappen'); ?></h1>
<img class="emblem-big" src="/images/emblems/<?php echo EmblemController::getEmblemByTeamIds($cookie_team); ?>" />
<form enctype="multipart/form-data" action="/ver_settings.php" method="POST">
    <input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
    <p><?php echo _('Datei auswählen:'); ?> <input name="emblem" type="file" /></p>
    <p><input type="submit" value="<?php echo _('Speichern'); ?>" /></p>    
</form>
<p><?php echo $result; ?></p>
<?php } else { ?>
<p><?php echo _('Du musst angemeldet sein, um diese Seite aufrufen zu können!'); ?></p>
<?php } ?>
<?php include 'zz3.php'; ?>