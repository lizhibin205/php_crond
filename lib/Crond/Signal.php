<?php
namespace Crond;

class Signal
{
    public static function register($signal, $callback)
    {
        return pcntl_signal($signal, $callback);
    }
}
