<?php
/**
 * task配置文件
 * 例子：
 * 'demo' => [
 *      'daemon' => '* * * * * *',//秒 分 时 日 月 周
 *      'filename' => '/usr/local/php/bin/php', //执行程序
 *      'params' => [],//执行程序参数
 *      'single' => true,//如果进程在运行，则不执行，只保持一个进程
 *      'standard_ouput' => '', //标准输出
 *      'error_output' => '', // 错误输出
 *  ]
 */

return [
    'demo' => [
        'daemon' => '*/3 * * * * *',
        'filename' => 'echo',
        'params' => ['"hello world!"'],
        'single' => true,
        'standard_ouput' => '/dev/null',
        'error_output' => '/dev/null',
    ]
];
