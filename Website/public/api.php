<?php
@session_start();

date_default_timezone_set('UTC');

require_once(__DIR__ . '/../bootstrap/autoload.php');
require_once(__DIR__ . '/../bootstrap/routes.php');

Router::dispatch();
