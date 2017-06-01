<?php
/**
 * 基于pcntl扩展实现的php crontab管理
 * @author 黎志斌
 * @daate 2017年5月25日
 */
use Crond\Signal;
use Crond\Task\Main;

if (PHP_SAPI !== 'cli') {
    echo 'php crontab must run in cli!', PHP_EOL;
    exit;
}

if (!function_exists('pcntl_exec')) {
    echo 'pcntl model not exists!', PHP_EOL;
    exit;
}

define('PROJECT_ROOT', dirname(__DIR__));
require __DIR__ . "/../vendor/autoload.php";

//注册信号函数
//用于安全关闭任务
$result = Signal::register(SIGUSR1, function($signal){
    echo "please wait, shuting down the crond...", PHP_EOL;
    Main::shutdown();
});

Main::start();
