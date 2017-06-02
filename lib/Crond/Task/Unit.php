<?php
namespace Crond\Task;

/**
 * 任务单元
 * @author lizhibin
 *
 */
class Unit
{
    /**
     * 任务唯一标识
     * @var string
     */
    private $taskName;

    /**
     * 执行程序路径
     * @var string
     */
    private $filename;

    /**
     * 程序输入参数
     * @var array
     */
    private $params;

    /**
     * 执行的秒
     * @var int
     */
    private $execSecond;

    /**
     * 执行的分钟
     * @var int
     */
    private $execMintue;

    /**
     * 执行的小时
     * @var int
     */
    private $execHour;

    /**
     * 执行的日期
     * @var int
     */
    private $execDay;

    /**
     * 执行的月份
     * @var int
     */
    private $execMonth;

    /**
     * 执行的一周的某天
     * @var int
     */
    private $execWeek;

    /**
     * 是否保持进程单独执行
     * @var bool
     */
    private $single = false;

    /**
     * 构造函数
     * @param string $taskName 任务唯一标识
     * @param stirng $filename 执行程序路径
     * @param array $params 程序输入参数
     * @param int $execSecond 执行的秒
     * @param int $execMintue 执行的分钟
     * @param int $execHour 执行的小时
     * @param int $execDay 执行的日期
     * @param int $execMonth 执行的月份
     * @param int $execWeek 执行的一周的某天
     */
    public function __construct($taskName, $filename, array $params, $execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek)
    {
        $this->taskName = $taskName;
        $this->filename = $filename;
        $this->params = $params;
        $this->execSecond = $execSecond;
        $this->execMintue = $execMintue;
        $this->execHour = $execHour;
        $this->execDay = $execDay;
        $this->execMonth = $execMonth;
        $this->execWeek = $execWeek;
    }

    /**
     * 设置保持进程单独执行
     * @param bool $single
     */
    public function setSingle($single)
    {
        $this->single = $single;
    }

    /**
     * 判断任务是否需要执行
     * @param int $execSecond
     * @param int $execMintue
     * @param int $execHour
     * @param int $execDay
     * @param int $execMonth
     * @param int $execWeek
     * @return bool 需要执行任务返回true，否则返回false
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
            //任务设置为*/n
            if (\preg_match("/^\*\/(\d+)$/", $configTime, $matches) === 1) {
                if ($nowTime % $matches[1] === 0) {
                    continue;
                } else {
                    return false;
                }
            }
            //任务设置为数字
            if (intval($configTime) === $nowTime) {
                continue;
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * 返回执行任务的参数
     * @return array
     */
    public function getExec()
    {
        return [$this->filename, $this->params];
    }

    /**
     * 获得任务名称
     * @return string
     */
    public function getTaskName()
    {
        return $this->taskName;
    }

    /**
     * 获取是否单独进程
     * @return bool 如果是单独进程，返回true
     */
    public function isSingle()
    {
        return $this->single;
    }
}