<?php
namespace Crond\Task;

/**
 * 任务配置读取类
 * @author lizhibin
 *
 */
class Config
{
    /**
     * 任务数据
     * @var array
     */
    private static $taskData = [];

    /**
     * 读取task的任务配置
     * @return void
     */
    public static function read()
    {
        $configFilename = PROJECT_ROOT . "/config/task.php";
        if (!is_file($configFilename)) {
            return ;
        }
        $configTaskList = include $configFilename;
        foreach ($configTaskList as $taskName => $task) {
            list($execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek) = \explode(' ', $task['daemon']);
            $unit = new Unit($taskName, $task['filename'], $task['params'], $execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek);
            $unit->setSingle($task['single']);
            $unit->setOuput($task['standard_ouput'], $task['error_output']);
            self::$taskData[] = $unit;
        }
    }

    /**
     * 重新加载task的任务配置
     * @return void
     */
    public static function reload()
    {
        self::$taskData = [];
        self::read();
    }

    /**
     * 查找需要执行的任务
     * @param int $execSecond 执行的秒
     * @param int $execMintue 执行的分钟
     * @param int $execHour 执行的小时
     * @param int $execDay 执行的日期
     * @param int $execMonth 执行的月份
     * @param int $execWeek 执行的一周的某天
     * @return array
     */
    public static function find($execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek)
    {
        foreach (self::$taskData as $task) {
            if ($task->match($execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek)) {
                yield $task;
            }
        }
    }
}