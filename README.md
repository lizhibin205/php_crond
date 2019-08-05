php_crond 
=============
基于多进程的定时任务管理器，支持秒级别的定时任务

特性
---------------
+ 通过配置文件管理所有定时任务
+ 支持秒级的定时任务粒度
+ 使用symfony/process进行进程管理
+ 使用React/event-loop执行事件循环
+ 提供http服务，远程查看和操作php_crond（[参见：API文档](https://github.com/lizhibin205/php_crond/wiki/API%E6%8E%A5%E5%8F%A3%E6%96%87%E6%A1%A3)）

执行
---------------
启动crond
```shell
php bin/crond.php
```
在后台启动crond
```shell
nohup php bin/crond.php > /dev/null 2>&1 &
```

发送USR1信号，安全关闭crond
主进程会等待所有的子进程任务结束，才会正式退出
```shell
kill -USR1 `cat logs/crond.pid`
```

发送USR2信号，重新读取task配置文件
```shell
kill -USR2 `cat logs/crond.pid`
```

基本配置
---------------
服务配置文件config/base.php
```php
return [
    //模式
    'model' => 'daemon',
    //PHP程序路径
    'php' => '/usr/local/php-5.6.30/bin/php',
    //日志文件
    'log_file' => PROJECT_ROOT . "/logs/crond.log",
    //主进程pid文件
    'pid_file' => PROJECT_ROOT . "/logs/crond.pid",
    //http接口服务，提供接口远程操作php_crond
    'http_server' => [
        'switch' => false,//是否启动http服务
        'listen' => '127.0.0.1',
        'port' => 8080,//监听端口
    ],
];
```

任务配置
---------------
任务配置文件config/task.php

```php
/**
 * task配置文件
 * 例子：
 */
return ['demo' => [
  'daemon' => '* * * * * *',//秒 分 时 日 月 周
  'filename' => 'sleep', //执行程序
  'params' => ['5'],//执行程序参数
  'single' => true,//如果进程在运行，则不执行，只保持一个进程
  'standard_ouput' => '/dev/null', //标准输出
  'error_output' => '/dev/null', // 错误输出
]];

```

如果你需要配置非常多的任务，可以使用Storage\Reader::registerTaskDirectory，该方法会遍历注册目录下的所有.php文件，并返回其中的任务列表

```php
return \Storage\Reader::registerTaskDirectory(__DIR__ . "/tasks");
```

允许配置外部接口，用于返回任务列表。参数url=接口地址，serverId=作为服务标识

PS：你可能需要额外搭建后台用于任务管理

```php
return \Storage\Reader::registerTaskRemote($url, $serverId);
```

接口返回例子

```javascript
{
    "demo": {
        "daemon": "0 * * * * *",
        "filename": "echo",
        "params": ["hello world!"],
        "single": true,
        "standard_ouput": "/dev/null",
        "error_output": "/dev/null"
    }
}
```