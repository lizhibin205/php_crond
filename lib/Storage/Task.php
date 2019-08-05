<?php
namespace Storage;

use Storage\Exception\TaskException;

class Task
{
    /**
     * 任务名称
     * @var string
     */
    private $name;

    /**
     * 任务数据
     * @var array
     */
    private $data;

    public function __construct($name, $data)
    {
        $this->name = $name;
        $this->data = $data;
    }

    /**
     * 创建一个Task对象
     * @param array $data
     * @throws TaskException
     * @return Task
     */
    public static function create($name, array $task)
    {
        if (!is_string($name) || empty($name)) {
            throw new TaskException("Task name must be string and can not be empty.");
        }

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

        return new Task($name, $task);
    }

    /**
     * 获取任务属性值
     * @param string $attr
     */
    public function __get($attr)
    {
        $dataAttr = $this->data;
        if (!isset($dataAttr[$attr])) {
            throw new TaskException("task[{$attr}] not exists.");
        }
        return $dataAttr[$attr];
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

    /**
     * 判断任务是否单例
     */
    public function isSingle()
    {
        return $this->single;
    }

    /**
     * 获取任务名称
     * @return string
     */
    public function getTaskName()
    {
        return $this->name;
    }

    /**
     * 获取唯一的任务名称
     * @return string
     */
    public function getUniqTaskName()
    {
        return $this->single ? $this->name : $this->name . time();
    }

    /**
     * 获取执行命令
     * @return string
     */
    public function getExecution()
    {
        return "{$this->filename} " . implode(" ", $this->params);
    }

    /**
     * 获取标准输出
     * @return string
     */
    public function getStandardOuput()
    {
        return $this->standard_ouput;
    }

    /**
     * 获取错误输出
     * @return string
     */
    public function getErrorOutput()
    {
        return $this->error_ouput;
    }
}