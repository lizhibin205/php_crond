<?php
namespace Crond;

class Signal
{
    public static function registerAll(Crond $crond)
    {
        if (PHP_OS === 'WINNT') {
            return;
        }
        //用户安全关闭-USR1
        pcntl_signal(SIGUSR1, function($signal) use($crond) {
            $crond->shutdown();
        });
        //用户重载配置文件-USR2
        pcntl_signal(SIGUSR2, function($signal) use($crond) {
            $crond->reloadTask();
        });
        //接收子进程结束的信号
        pcntl_signal(SIGCHLD, function($signal) use($crond) {
            $crond->waitProcess();
        });
    }

    /**
     * 处理挂起的信号
     */
    public static function dispatch()
    {
        if (PHP_OS !== 'WINNT') {
            pcntl_signal_dispatch();
        }
    }
}
