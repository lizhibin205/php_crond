<?php
namespace Stroage;

use Stroage\Exception\TaskException;

class Task
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * 创建一个Task对象
     * @param array $data
     * @throws \Stroage\Exception\TaskException
     * @return Task
     */
    public static function create(array $task)
    {
        //必须参数
        foreach (['daemon', 'filename', 'params', 'single', 'standard_ouput', 'error_output'] as $field) {
            if (!isset($task[$field])) {
                throw new TaskException("Task {$field} missed. Please check your configuration.");
            }
        }

        //验证daemon配置
        if (!is_string($task['daemon']) || count(explode(' ', $task['daemon'])) != 6) {
            throw new TaskException("task.daemon configuration error.");
        }

        //验证param配置
        if (!is_array($task['params'])) {
            throw new TaskException("task.params must be array.");
        }

        //验证是否单例配置
        if (!is_bool($task['single'])) {
            throw new TaskException("task.single must be boolean.");
        }

        return new Task($task);
    }

    /**
     * 获取任务属性值
     * @param string $attr
     */
    public function __get($attr)
    {
        $data = $this->data;
        if (!isset($data[$attr])) {
            throw new TaskException("task.{$attr} not exists.");
        }
        return $data[$attr];
    }

    /**
     * 验证任务在当前时间是否需要执行
     * @param int $execSecond
     * @param int $execMintue
     * @param int $execHour
     * @param int $execDay
     * @param int $execMonth
     * @param int $execWeek
     * @return boolean
     */
    public function match($execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek)
    {
        $timeArr = ['execSecond', 'execMintue', 'execHour', 'execDay', 'execMonth', 'execWeek'];
        foreach ($timeArr as $time) {
            $nowTime = intval($$time);
            $configTime = $this->$time;
            //任务设置为*，通过
            if ($configTime === '*') {
                continue;
            }
            $configTimeList = explode(',', $configTime);
            $configTimeMatch = false;
            foreach ($configTimeList as $configTimePart) {
                //任务设置为*/n
                $matches = null;
                if (\preg_match("/^\*\/(\d+)$/", $configTimePart, $matches) === 1) {
                    if ($nowTime % $matches[1] === 0) {
                        $configTimeMatch = true;
                        break;
                    }
                }
                //任务设置为数字
                if (intval($configTimePart) === $nowTime) {
                    $configTimeMatch = true;
                    break;
                }
            }
            if ($configTimeMatch === false) {
                return false;
            }
        }
        return true;
    }
}