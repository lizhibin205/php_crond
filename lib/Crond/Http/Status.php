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
        if (is_numeric($pid)) {
            $result = [
                'pid_file' => file_get_contents(\Crond\Config::attr('pid_file')),
                'task_list' => \Crond\Task\Config::getTaskList()
            ];
        } else {
            $result = [
                'msg' => 'php_crond is not running'
            ];
        }

        return $result;
    }
}