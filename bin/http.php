<?php
/**
 * 基于pcntl扩展实现的php crontab管理 - web服务
 * 用户查询，操作php_crond
 * @author 黎志斌
 * @daate 2017年8月5日
 */
use Crond\Http\Main;

if (PHP_SAPI !== 'cli') {
    echo 'php crontab web must run in cli!', PHP_EOL;
    exit;
}

define('PROJECT_ROOT', dirname(__DIR__));
require __DIR__ . "/../vendor/autoload.php";

Main::start();