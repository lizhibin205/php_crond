<?php
namespace Crond\Http;

class Status
{
    public function __construct(){}

    /**
     * 返回php_crond的执行状态
     * @return mixeds 
     */
    public function index()
    {
        $result = [
            'pid_file' => file_get_contents(\Crond\Config::attr('pid_file')),
            'task_list' => \Crond\Task\Config::getTaskList()
        ];
        return $result;
    }
}