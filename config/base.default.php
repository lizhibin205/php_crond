<?php
return [
    //模式
    'model' => 'daemon',
    //日志文件
    'log_file' => PROJECT_ROOT . "/logs/crond.log",
    //主进程pid文件
    'pid_file' => PROJECT_ROOT . "/logs/crond.pid",
    //http server
    'http_switch' => false,
    'http_listen' => '127.0.0.1',
    'http_port'   => 8080,
    'http_log'    => PROJECT_ROOT . "/logs/http.log"
];