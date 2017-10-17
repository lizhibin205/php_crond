<?php
namespace Crond\Http;

final class Status extends Controller
{
    /**
     * 返回php_crond的执行状态
     * @return mixeds 
     */
    public function index()
    {
        $pid = \Crond\Task\Main::getPid();

        $result = [
            'pid' => $pid,
            'task_list' => \Crond\Task\Config::getTaskList()
        ];

        return $result;
    }
}