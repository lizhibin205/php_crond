<?php
return [
    //模式
    'model' => 'daemon',
    //日志文件
    'log_file' => PROJECT_ROOT . "/logs/crond.log",
    //主进程pid文件
    'pid_file' => PROJECT_ROOT . "/logs/crond.pid",
    //http server
    'http_server' => [
        'switch' => true,
        'listen' => '127.0.0.1',
        'port' => 8080,
    ],
];