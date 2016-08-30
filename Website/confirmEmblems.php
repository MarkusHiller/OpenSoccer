<?php 
include 'zz1.php'; 
require_once 'controller/emblemController.php';
require_once 'controller/teamController.php';
require_once 'controller/userController.php';
include 'zz2.php';
?>
<title><?php echo _('Wappen bestätigen'); ?> - <?php echo CONFIG_SITE_NAME; ?></title>

<?php
if ($loggedin == 1) {
if ($_SESSION['status'] != 'Helfer' && $_SESSION['status'] != 'Admin') { exit; }
if (isset($_GET['confirm']) && isset($_GET['ids'])) {
	EmblemController::handleConfirm($_GET['confirm'], $_GET['ids']);
}
$unconfirmed = EmblemController::getUnconfirmedEmblems();
?>
<!--FORM -->
<h1><?php echo _('Unbestätigte Wappen'); ?></h1>
<?php
while ($emblem = mysql_fetch_object($unconfirmed)) {
?>
<div>
	<img class="emblem-big" src="/images/emblems/<?php echo $emblem->team.'.png'; ?>" />
	<div style="padding-top:30px;">
		<div>
			<span><?php echo __('Team: '); ?></span>
			<a href="<?php echo '/team.php?id='.$emblem->team; ?>"><?php echo TeamController::getTeamNameByIds($emblem->team); ?></a>
		</div>
		<div style="padding-bottom: 20px;">
			<span><?php echo __('Manager: '); ?></span>
			<?php echo UserController::getManagerLinkByTeamIds($emblem->team); ?>
		</div>
		<a class="pagenava" href="/confirmEmblems.php?confirm=true&ids=<?php echo $emblem->team; ?>">Freigeben</a>
		<a class="pagenava" href="/confirmEmblems.php?confirm=false&ids=<?php echo $emblem->team; ?>">Löschen</a>
	</div>
</div>
<?php
}
?>
<?php

?>
<?php } else { ?>
<h1><?php echo _('Unbestätigte Wappen'); ?></h1>
<p><?php echo _('Du musst angemeldet sein, um diese Seite aufrufen zu können!'); ?></p>
<?php } ?>
<?php include 'zz3.php'; ?>
