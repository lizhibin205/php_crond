<?php 
use Crond\Task\Config;

define('PROJECT_ROOT', dirname(__DIR__));
require __DIR__ . "/../vendor/autoload.php";

//print_r(include PROJECT_ROOT . "/config/task.php");
$options = getopt("ha", [
    'linux-crontab', 'ignore-second', 'no-output'
]);

//if show help
if (isset($options['h'])) {
    show_help();
    exit;
}

//show tasks
$taskList = Config::getTaskList();
echo "There are ".count($taskList)." task(s)", PHP_EOL;
//output
if (isset($options['linux-crontab'])) {
    tasks_ouput_linux_crontab($taskList, isset($options['ignore-second']), isset($options['no-output']));
} else {
    //default format
    tasks_output($taskList);
}

function show_help()
{
    echo "Thanks for you use php_crond", PHP_EOL;
    echo "To get more information, you can visit https://github.com/lizhibin205/php_crond", PHP_EOL;
    echo "-h                get help", PHP_EOL;
    echo "-a                get all tasks information, default option", PHP_EOL;
    echo "--linux-crontab   show tasks list like linux crontab fotmat", PHP_EOL;
    echo "--ignore-second   if task second param not equals 0, it will be ignored (only used in linux-crontab)", PHP_EOL;
    echo "--no-output        without standard_ouput and error_output(only used in linux-crontab)", PHP_EOL;
}

function tasks_output($taskList)
{
    $header = ['daemon', 'execution', 'is_single', 'standard_ouput', 'error_output'];
    echo implode("\t", $header), PHP_EOL;
    foreach ($taskList as $task) {
        echo implode("\t", [
            $task['daemon'], "{$task['filename']} ". implode(' ', $task['params']), $task['single'] ? 'yes' : 'no',
            $task['standard_ouput'], $task['error_output']
        ]), PHP_EOL;
    }
}

function tasks_ouput_linux_crontab($taskList, $ignoreSecond = false, $noOuput = false)
{
    foreach ($taskList as $task) {
        $daemonFirstPos = strpos($task['daemon'], ' ');
        $daemonSecond = substr($task['daemon'], 0, $daemonFirstPos);
        $daemonLinuxCrontab = substr($task['daemon'], $daemonFirstPos + 1);
        if ($ignoreSecond && $daemonSecond != '0') {
            continue;
        }
        $params = implode(' ', $task['params']);
        $linuxCrontab = "{$daemonLinuxCrontab} {$task['filename']} {$params}";
        if (!$noOuput) {
            $linuxCrontab .= " >> {$task['standard_ouput']}";
        }
        echo $linuxCrontab, PHP_EOL;
    }
}