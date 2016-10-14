<?php
if (!isset($_GET['mode'])) {
    include 'zzserver.php';
}
require_once '/classes/ComputerManager.php';
new ComputerManager();
?>