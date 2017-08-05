<?php
return [
    //模式
    'model' => 'daemon',
    //PHP程序路径
    'php' => '/usr/local/php-5.6.30/bin/php',
    //日志文件
    'log_file' => PROJECT_ROOT . "/logs/crond.log",
    //主进程pid文件
    'pid_file' => PROJECT_ROOT . "/logs/crond.pid",
    //http server
    'http_server' => [
        'switch' => false,
        'listen' => '127.0.0.1',
        'port' => 8080,
    ],
];