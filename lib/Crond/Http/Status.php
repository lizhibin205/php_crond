<?php
namespace Crond\Http;

use Crond\Task\Main;

final class Status extends Controller
{
    /**
     * 返回php_crond的执行状态
     * @return mixed
     */
    public function index()
    {
        $pid = Main::getPid();

        $result = [
            'pid' => $pid,
            'task_list' => Main::getTaskListStatus(),
        ];

        return $result;
    }
}