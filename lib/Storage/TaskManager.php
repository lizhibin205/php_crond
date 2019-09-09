<?php
namespace Storage;

use Crond\Exception\CrondRuntimeException;

class TaskManager
{
    private $list = [];

    /**
     * 加载任务
     */
    public function loadTasks()
    {
        $filename = PROJECT_ROOT . "/config/task.php";
        if (!is_file($filename)) {
            throw new CrondRuntimeException("Counld not load task config file.");
        }
        $taskArrList = include PROJECT_ROOT . "/config/task.php";
        foreach ($taskArrList as $taskName => $taskArr) {
            $this->addTask(Task::create($taskName, $taskArr));
        }
        return $this;
    }

    /**
     * 重新加载任务
     */
    public function reloadTasks()
    {
        $this->list = [];
        $this->loadTasks();
    }

    /**
     * 添加任务
     * @param Task $task
     */
    public function addTask(Task $task)
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
}

