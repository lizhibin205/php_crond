<?php
namespace Crond\Task;

use \Symfony\Component\Process\Process;

class Main
{
    /**
     * 任务执行状态，进程没有执行
     * @var int
     */
    const TASK_NONE = 0;

    /**
     * 任务执行状态，进程正在运行
     * @var int
     */
    const TASK_EXEC = 1;

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
        $loop->addPeriodicTimer(1, function($timer) use ($crondTaskMain, $logger, $loop){
            list($execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek) = \explode(' ', date("s i H d m w"));
            //执行及具体任务
            $taskList = Config::find($execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek);
            foreach ($taskList as $task) {
                //获取任务的唯一名称
                $taskUniqName = $task->getUniqTaskName();

                //判断是否single的任务
                if ($task->isSingle() && $crondTaskMain->checkProcess($taskUniqName) === Main::TASK_EXEC) {
                    $logger->info($task->getTaskName() . " is running");
                    continue;
                }

                list($processFilename, $params) = $task->getExec();
                $process = new Process("{$processFilename} " . \implode(' ', $params));
                $process->start(function ($type, $buffer) use($task) {
                    if ($type === Process::ERR) {
                        $filename = $task->getStderr();
                    } else {
                        $filename = $task->getStdout();
                    }
                    \file_put_contents($filename, $buffer, FILE_APPEND);
                });
                $logger->info($task->getTaskName() . " start");
                $crondTaskMain->markProcess($taskUniqName, $process);
            }
            //执行具体任务结束
            //信号处理
            pcntl_signal_dispatch();
            //信号处理结束
            if (!Main::getInstance()->alive()) {
                $loop->cancelTimer($timer);
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
     * 安全终止定时任务
     * @return void
     */
    public static function shutdown()
    {
        self::getInstance()->running = false;
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
     * 检查任务执行状态
     * @param string $taskUniqName 任务唯一名称
     * @return int 任务状态
     */
    private function checkProcess($taskUniqName)
    {
        if (!isset($this->processList[$taskUniqName])) {
            return self::TASK_NONE;
        }
        $process = $this->processList[$taskUniqName];
        if ($process->isRunning()) {
            return self::TASK_EXEC;
        } else {
            unset($this->processList[$taskUniqName]);
            return self::TASK_NONE;
        }
    }
}