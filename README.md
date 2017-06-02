php_crontab 
=============
基于php pcntl的定时任务管理器，支持秒级别的定时任务

特性
---------------
+ 通过配置文件管理所有定时任务
+ 支持秒级的定时任务粒度
+ 多进程执行任务
+ 使用React/event-loop执行事件循环

执行
---------------
```shell
php bin/crond.php
```

配置
---------------
任务配置文件config/task.php
```php
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
        'daemon' => '*/5 * * * * *',
        'filename' => '/usr/local/php-5.6.30/bin/php',
        'params' => ['/www/tests/pcntl/examples/a.php'],
        'single' => true
    ]
];
```