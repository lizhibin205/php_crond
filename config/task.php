<?php
/**
 * task配置文件
 * 例子：
 * 'process_a' => [
 *      'daemon' => '* * * * * *',//秒 分 时 日 月 周
 *      'filename' => '/usr/local/php/bin/php', //执行程序
 *      'params' => [],//执行程序参数
 *      'single' => true,//如果进程在运行，则不执行，只保持一个进程
 *  ]
 */

return [
    'process_a' => [
        'daemon' => '*/3 * * * * *',
        'filename' => '/usr/local/php-5.6.30/bin/php',
        'params' => ['/www/tests/pcntl/examples/a.php'],
        'single' => true
    ]
];
