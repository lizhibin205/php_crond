<?php
namespace Storage;

use Crond\Exception\CrondRuntimeException;
use Monolog\Logger;
use Storage\Security\CommandRule;

class TaskManager
{
    private $list = [];

    /**
     * 加载任务
     */
    public function loadTasks(Logger $logger) : TaskManager
    {
        $taskFilename = PROJECT_ROOT . "/config/task.php";
        if (!is_file($taskFilename)) {
            throw new CrondRuntimeException("counld not load task config file.");
        }
        $securityCommandList = [];
        $securityFilename = PROJECT_ROOT . "/config/security.php";
        if (is_file($securityFilename)) {
            $securityCommandList = include $securityFilename;
            if (!is_array($securityCommandList)) {
                throw new CrondRuntimeException("security file must be return array.");
            }
            foreach ($securityCommandList as $securityCommand) {
                if (!($securityCommand instanceof CommandRule)) {
                    throw new CrondRuntimeException("security command element must be implements CommandRule.");
                }
            }
        }

        $taskArrList = include $taskFilename;
        foreach ($taskArrList as $taskName => $taskArr) {
            $task = Task::create($taskName, $taskArr);
            //安全性检测
            $securityPass = false;
            foreach ($securityCommandList as $securityCommand) {
                if ($securityCommand->check($task->filename)) {
                    $securityPass = true;
                    break;
                }
            }
            if ($securityPass) {
                $this->addTask($task);
            }
            $logger->warning("task {$taskName} security unpass.");
        }
        return $this;
    }

    /**
     * 重新加载任务
     */
    public function reloadTasks() : void
    {
        $this->list = [];
        $this->loadTasks();
    }

    /**
     * 添加任务
     * @param Task $task
     */
    public function addTask(Task $task) : void
    {
        $this->list[] = $task;
    }

    /**
     * 查找符合条件的任务
     * @return Task
     */
    public function findTask($execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek)
    {
        foreach ($this->list as $task) {
            if ($task->match($execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek)) {
                yield $task;
            }
        }
    }

    /**
     * 遍历任务列表中的任务
     * @return Task
     */
    public function fetchTask()
    {
        foreach ($this->list as $task) {
            yield $task;
        }
    }

    /**
     * 返回当前配置的任务列表
     * @return array
     */
    public function getAllTasks() : array
    {
        $list = [];
        foreach ($this->list as $task) {
            $list[] = $task->getData();
        }
        return $list;
    }
}

