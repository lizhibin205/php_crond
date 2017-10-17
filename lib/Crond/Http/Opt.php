<?php
namespace Crond\Http;

final class Opt extends Controller
{
    /**
     * 安全关闭php_crond服务
     * return array
     */
    public function shutdown()
    {
        $pid = \Crond\Task\Main::getPid();
        if (is_numeric($pid) && posix_kill($pid, SIGUSR1)) {
            return [
                'code' => 1,
                'msg' => 'signal USR1 sent!'
            ];
        } else {
            return [
                'code' => -1,
                'msg' => 'error: signal USR1 sent failure!'
            ];
        }
    }

    /**
     * 重新加载任务列表
     * @return array
     */
    public function reload()
    {
        $pid = \Crond\Task\Main::getPid();
        if (is_numeric($pid) && posix_kill($pid, SIGUSR2)) {
            return [
                'code' => 1,
                'msg' => 'signal USR2 sent!'
            ];
        } else {
            return [
                'code' => -1,
                'msg' => 'error: signal USR2 sent failure!'
            ];
        }
    }

    /**
     * 添加计划任务
     * @return array
     */
    public function add()
    {
        $getParams = $this->request->getQueryParams();
        $taskName = $getParams['task_name'];
        $params = $getParams['params'];
        $task = [
            'daemon' => $getParams['daemon'],
            'filename' => $getParams['filename'],
            'params' => explode(' ', $params),
            'single' => $getParams['single'] === '1' ? true : false,
            'standard_ouput' => $getParams['standard_ouput'],
            'error_output' => $getParams['error_output'],
        ];

        //写入添加文件
        \Crond\Task\Config::addTask($taskName, $task);
        return [
            'code' => 1,
            'msg' => "add task[{$taskName}] success!"
        ];
    }

    /**
     * 移除任务
     * @return array
     */
    public function remove()
    {
        $taskName = $this->request->getQueryParams()['task_name'];
        \Crond\Task\Config::removeTask($taskName);
    }
}