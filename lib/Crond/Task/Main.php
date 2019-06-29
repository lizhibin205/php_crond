<?php
namespace Crond\Task;

use Http\Server;
use Symfony\Component\Process\Process;
use Psr\Http\Message\ServerRequestInterface;

class Main
{
    /**
     * 存储Main对象的单例
     * @var Main
     */
    private static $main;

    /**
     * 进程执行列表
     * @var array
     */
    private $processList = [];

    /**
     * 定时任务执行状态
     * @var boolean
     */
    private $running = true;

    private function __construct(){}
    private function __clone(){}

    /**
     * 启动定时任务
     * @return void
     */
    public static function start()
    {
        //获取Crond的配置信息
        Config::read();
        //初始化进程管理类
        $crondTaskMain = self::getInstance();
        //创建PID文件
        $crondTaskMain->createPidFile(\Crond\Config::attr('pid_file'));
        //日志记录器
        $logger = new \Monolog\Logger('crond');
        $logger->pushHandler(new \Monolog\Handler\StreamHandler(\Crond\Config::attr('log_file'), \Monolog\Logger::INFO));

        //程序开始记录日志
        $logger->info("php_crond start");
        //主进程循环执行任务
        $loop = \React\EventLoop\Factory::create();
        //HTTP启动
        $httpConfig = \Crond\Config::attr('http_server');
        if ($httpConfig['switch'] === true) {
            $httpServer = Server::createHttpServer();
            $socket = new \React\Socket\Server($httpConfig['port'], $loop);
            $httpServer->listen($socket);
        }
        //主进程定时器
        $loop->addPeriodicTimer(1, function($timer) use ($crondTaskMain, $logger, $loop){
            list($execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek) = \explode(' ', date("s i H d m w"));
            //执行及具体任务
            $taskList = Config::find($execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek);
            foreach ($taskList as $task) {
                //获取任务的唯一名称
                $taskUniqName = $task->getUniqTaskName();

                //判断是否single的任务
                if ($task->isSingle()) {
                    $pid = $crondTaskMain->getProcessPidByUniqName($taskUniqName);
                    if ($pid > 0) {
                        //判断是否要杀死重启
                        if ($task->isSingleKillPervious()) {
                             $exitCode = $crondTaskMain->stopProcessByUniqName($taskUniqName);
                             $logger->info($task->getTaskName() . "killed by single kill pervious, and exit code :{$exitCode}");
                        } else {
                            $logger->info($task->getTaskName() . " is running(pid={$pid})");
                            continue;
                        }
                    }
                }

                list($processFilename, $params) = $task->getExec();
                $processCommand = "{$processFilename} " . \implode(' ', $params);
                $process = new Process($processCommand);
                $process->start(function ($type, $buffer) use($task) {
                    if ($type === Process::ERR) {
                        $filename = $task->getStderr();
                    } else {
                        $filename = $task->getStdout();
                    }
                    \file_put_contents($filename, $buffer, FILE_APPEND);
                });
                $logger->info($task->getTaskName() . "[{$processCommand}] start");
                $crondTaskMain->markProcess($taskUniqName, $process);
            }
            //执行具体任务结束
            //信号处理
            if (PHP_OS !== 'WINNT') {
                pcntl_signal_dispatch();
            }
            //信号处理结束
            if (!Main::getInstance()->alive()) {
                $loop->cancelTimer($timer);
                $loop->stop();
            }
        });
        $loop->run();
        //主进程循环执行任务结束

        //等待所有子进程结束，结束进程
        while ($crondTaskMain->isTasksAlive()) {
            sleep(1);
        }
    }

    /**
     * 查询主任务运行状态
     * @return boolean
     */
    public static function getRunStatus()
    {
        return self::getInstance()->running;
    }

    /**
     * 返回任务列表
     * @return array
     */
    public static function getTaskListStatus()
    {
        $taskList =  Config::getTaskList();
        foreach ($taskList as $taskName => &$task) {
            $task['pid'] = self::getInstance()->getProcessPidByUniqName($taskName);
        }
        unset($task);
        return $taskList;
    }

    /**
     * 安全终止定时任务
     * @return void
     */
    public static function shutdown()
    {
        self::getInstance()->running = false;
    }

    /**
     * 获取php_crond的pid
     * return string 返回php_crond的pid
     */
    public static function getPid()
    {
        $pidFilename = \Crond\Config::attr('pid_file');
        return is_file($pidFilename) ? file_get_contents($pidFilename) : 0;
    }

    /**
     * 重新加载任务配置文件
     * @return void
     */
    public static function reloadTask()
    {
        Config::reload();
    }

    /**
     * 处理子进程发送的SIGCHLD，防止僵尸进程
     * @return void
     */
    public static function waitProcess()
    {
        $main = self::getInstance();
        foreach ($main->processList as $process) {
            $process->isTerminated();
        }
    }

    /**
     * 初始化定时任务对象实例
     * @return Main
     */
    private static function getInstance()
    {
        if (!(self::$main instanceof self)) {
            self::$main = new self();
        }
        return self::$main;
    }

    /**
     * 返回任务的执行状态
     * @return bool 如果正在执行，返回true，否则返回false
     */
    public function alive()
    {
        return $this->running;
    }

    /**
     * 检测是否有任务在执行
     * @return bool 如果有任务执行，返回true，否则返回false
     */
    public function isTasksAlive()
    {
        foreach ($this->processList as $process) {
            if ($process->isRunning()) {
                return true;
            }
        }
        return false;
    }

    /**
     * 创建pid文件
     * @param string $pidFileName pid文件路径
     * @throws \RuntimeException
     * @return void
     */
    private function createPidFile($pidFileName)
    {
        if (\is_file($pidFileName)) {
            throw new \RuntimeException("pid file is exists, check the crond php is running or not!");
        }
        
        $pid = \getmypid();
        if (!\file_put_contents($pidFileName, $pid)) {
            throw new \RuntimeException("counldn't create pid file!");
        }
        register_shutdown_function(function($pidFileName){
            unlink($pidFileName);
        }, $pidFileName);
        
    }

    /**
     * 记录任务执行状态
     * @param string $taskUniqName 任务唯一名称
     * @param int $childPid 进程ID
     */
    private function markProcess($taskUniqName, Process $process)
    {
        $this->processList[$taskUniqName] = $process;
    }

    /**
     * 获取任务的pid
     * @param string $taskUniqName
     * @return int
     */
    private function getProcessPidByUniqName($taskUniqName)
    {
        if (!isset($this->processList[$taskUniqName])) {
            return 0;
        }
        $process = $this->processList[$taskUniqName];
        $pid = $process->getPid();
        return is_numeric($pid) ? $pid : 0;
    }

    /**
     * 根据任务名终止进程
     * @param string $taskUniqName
     * @return int
     */
    private function stopProcessByUniqName($taskUniqName)
    {
        if (!isset($this->processList[$taskUniqName])) {
            return 0;
        }
        $process = $this->processList[$taskUniqName];
        //这里使用参数0，避免阻塞主进程
        return $process->stop(0);
    }
}