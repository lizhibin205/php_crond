<?php
namespace Crond;

class Signal
{
    /**
     * 注册信号时间
     * @param int $signal
     * @param callback $callback
     * @return boolean
     */
    public static function register($signal, $callback)
    {
        if (PHP_OS !== 'WINNT') {
            return pcntl_signal($signal, $callback);
        }
        return false;
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
