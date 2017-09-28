<?php 
define('PROJECT_ROOT', dirname(__DIR__));
require __DIR__ . "/../vendor/autoload.php";

print_r(include PROJECT_ROOT . "/config/task.php");