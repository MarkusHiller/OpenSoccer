<?php

if (!isset($_GET['mode'])) {
    include_once(__DIR__.'/zzserver.php');
}
require_once(__DIR__.'/controller/playerController.php');

PlayerController::addJugendspielerToTransferlist();

?>

