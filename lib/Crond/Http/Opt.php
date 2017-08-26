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
        //判断接口请求方式
        if ($this->request->getMethod() !== "POST") {
            return [
                'code' => -1,
                'msg' => 'error: bad request method!'
            ];
        }

        $postParams = $this->request->getParsedBody();
        $taskName = $postParams['task_name'];
        $task = [
            'daemon' => $postParams['daemon'],
            'filename' => $postParams['filename'],
            'params' => is_array($postParams['params']) ? $postParams['params'] : [$postParams['params']],
            'single' => $postParams['single'] === '1' ? true : false,
            'standard_ouput' => $postParams['standard_ouput'],
            'error_output' => $postParams['error_output'],
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