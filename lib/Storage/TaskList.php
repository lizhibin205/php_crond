<?php
namespace Storage;

use Crond\CrondRuntimeException;

class TaskList
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
     * @return array
     */
    public function findTask($execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek)
    {
        $matchTaskList = [];
        foreach ($this->list as $task) {
            if ($task->match($execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek)) {
                $matchTaskList[] = $task;
            }
        }
        return $matchTaskList;
    }
}

