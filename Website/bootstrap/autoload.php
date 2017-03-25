<?php

require_once(__DIR__ . '/../Helpers/database.php');
require_once __DIR__ . '/../Logger/Log.php';

spl_autoload_register("autoloadController");
spl_autoload_register("autoloadRouter");
spl_autoload_register("autoloadHelpers");
spl_autoload_register("autoloadClasses");
spl_autoload_register("autoloadModels");

function autoloadController($className) {
    $filename = __DIR__ . "/../controller/" . $className . ".php";
    if (is_readable($filename)) {
        require_once $filename;
    }
}

function autoloadRouter($className) {
    $filename = __DIR__ . "/../Router/" . $className . ".php";
    if (is_readable($filename)) {
        require_once $filename;
    }
}

function autoloadHelpers($className) {
    $filename = __DIR__ . "/../Helpers/" . $className . ".php";
    if (is_readable($filename)) {
        require_once $filename;
    }
}

function autoloadClasses($className) {
    $filename = __DIR__ . "/../classes/" . $className . ".php";
    if (is_readable($filename)) {
        require_once $filename;
    }
}

function autoloadModels($className) {
    $filename = __DIR__ . "/../Models/" . $className . ".php";
    if (is_readable($filename)) {
        require_once $filename;
    }
}