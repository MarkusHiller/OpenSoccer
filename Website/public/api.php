<?php
@session_start();

require_once(__DIR__ . '/../bootstrap/autoload.php');
require_once(__DIR__ . '/../bootstrap/routes.php');

Router::dispatch();
