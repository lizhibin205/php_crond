<?php
/**
 * 基于pcntl扩展实现的php crontab管理
 * @author 黎志斌
 * @daate 2017年5月25日
 */
use Crond\Crond;
use Storage\TaskManager;

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

$options = getopt("ha", [
    'linux-crontab', 'ignore-second', 'no-output'
]);
if (isset($options['h'])) {
    show_help();
    exit;
}
if (isset($options['a'])) {
    $taskManager = new TaskManager();
    $taskManager->loadTasks();
    foreach ($taskManager->fetchTask() as $task) {
        echo 'show all tasks: ', PHP_EOL;
        echo $task->daemon ." " .$task->getExecution(), PHP_EOL;
    }
    exit;
}

//执行计划任务
Crond::start();

/**
 * 展示help列表
 */
function show_help()
{
    echo "Thanks for you use php_crond", PHP_EOL;
    echo "run php_crond, exec php bin/crond.php", PHP_EOL;
    echo "To get more information, you can visit https://github.com/lizhibin205/php_crond", PHP_EOL;
    echo "-h                get help", PHP_EOL;
    echo "-a                get all tasks information", PHP_EOL;
}