<?php
namespace Storage;

class TaskList
{
    private $list = [];

    /**
     * 
     * @param Task $task
     */
    public function addTask(Task $task)
    {
        
    }

    /**
     * 查找符合条件的任务
     * @return array
     */
    public function findTask($execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek)
    {
        $matchTaskList = [];
        return $matchTaskList;
    }
}

