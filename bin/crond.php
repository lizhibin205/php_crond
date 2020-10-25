<?php
/**
 * 基于pcntl扩展实现的php crontab管理
 * @author 黎志斌
 * @daate 2017年5月25日
 */
use Crond\Crond;

if (PHP_SAPI !== 'cli') {
    echo 'php crontab must run in cli!', PHP_EOL;
    exit;
}

if (!function_exists('pcntl_exec') && PHP_OS !== 'WINNT') {
    echo 'pcntl model not exists!', PHP_EOL;
    exit;
}

define('PROJECT_ROOT', dirname(__DIR__));
require __DIR__ . "/../vendor/autoload.php";

//执行计划任务
Crond::start();
