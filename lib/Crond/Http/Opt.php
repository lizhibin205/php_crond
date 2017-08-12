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
        if (is_numeric($pid)) {
            posix_kill($pid, SIGUSR1);
            return [
                'msg' => 'signal USR1 sent!'
            ];
        } else {
            return [
                'msg' => 'error: pid file lost!'
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
        if (is_numeric($pid)) {
            posix_kill($pid, SIGUSR2);
            return [
                'msg' => 'signal USR2 sent!'
            ];
        } else {
            return [
                'msg' => 'error: pid file lost!'
            ];
        }
    }

    /**
     * 添加计划任务
     * @return array
     */
    public function add()
    {
        
    }

    /**
     * 移除任务
     * @return array
     */
    public function remove()
    {
        
    }
}