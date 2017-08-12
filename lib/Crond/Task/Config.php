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
        $configTaskList = self::getTaskList();
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

    /**
     * 获取任务配置文件的数据
     * return array
     */
    public static function getTaskList()
    {
        $configFilename = PROJECT_ROOT . "/config/task.php";
        if (!is_file($configFilename)) {
            return [];
        } else {
            return include $configFilename;
        }
    }

    /**
     * 添加任务
     * @param string $taskName 任务名称
     * @param array $task 任务参数
     * @throws \RuntimeException
     * @return void
     */
    public static function addTask($taskName, $task)
    {
        $httpConfig = \Crond\Config::attr("http_server");
        if (!isset($httpConfig['cache_dir']) || !is_dir($httpConfig['cache_dir'])) {
            throw new \RuntimeException("error:cache dir not exists!");
        }
        $cacheDir = $httpConfig['cache_dir'];

        if (preg_match("/^[A-Za-z0-9_]+$/", $taskName) === 0) {
            throw new \RuntimeException("{$taskName} contain special char(allow A-Z,a-z,0-9, and _)!");
        }

        $taskFile = "{$cacheDir}/{$taskName}.json";
        if (is_file($taskFile)) {
            throw new \RuntimeException("{$taskName} already exists!");
        }

        if (file_put_contents($taskFile, json_encode($task)) === false) {
            throw new \RuntimeException("save task[{$taskName}] failure!");
        }
    }

    /**
     * 移除任务
     * @param string $taskName
     * @throws \RuntimeException
     */
    public static function removeTask($taskName)
    {
        $httpConfig = \Crond\Config::attr("http_server");
        if (!isset($httpConfig['cache_dir']) || !is_dir($httpConfig['cache_dir'])) {
            throw new \RuntimeException("error:cache dir not exists!");
        }
        $cacheDir = $httpConfig['cache_dir'];

        if (preg_match("/^[A-Za-z0-9_]+$/", $taskName) === 0) {
            throw new \RuntimeException("{$taskName} contain special char(allow A-Z,a-z,0-9, and _)!");
        }

        $taskFile = "{$cacheDir}/{$taskName}.json";
        if (!unlink($taskFile)) {
            throw new \RuntimeException("remove task[{$taskName}] failure!");
        }
    }
}