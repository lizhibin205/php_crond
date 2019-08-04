<?php
namespace Crond;

use Symfony\Component\Process\Process;
use React\EventLoop\Factory;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Crond\Config;
use Storage\TaskList;

class Crond
{
    /**
     * 进程执行列表
     * @var array
     */
    private $processList = [];

    /**
     * 定时任务执行状态
     * @var boolean
     */
    private $running = false;

    /**
     * 计划任务配置
     * @var Config
     */
    private $crondConfig;

    /**
     * 计划任务列表
     * @var TaskList
     */
    private $taskList;

    /**
     * 日志
     * @var Logger
     */
    private $logger;

    /**
     * Crond
     */
    public function __construct(Config $crondConfig, TaskList $taskList)
    {
        $this->crondConfig = $crondConfig;
        $this->taskList = $taskList;
    }

    /**
     * 启动定时任务
     * @return void
     */
    public static function start()
    {
        //获取Crond启动配置
        $crondConfig = new Config();
        //获取任务列表
        $taskList = new TaskList();
        //日志记录器
        $logger = new Logger('crond');
        $logger->pushHandler(new StreamHandler($crondConfig->attr('log_file'), Logger::INFO));

        //初始化Crond实例
        $crond = new Crond($crondConfig, $taskList);
        $crond->setLogger($logger);
        $crond->run();
        return;
    }

    /**
     * 执行任务主循环
     */
    public function run()
    {
        try {
            //创建PID文件
            $this->createPidFile($this->crondConfig->attr('pid_file'));
            //程序开始记录日志
            $this->logger->info("php_crond start");
            //主进程循环执行任务
            $loop = Factory::create();
            //HTTP启动
            $httpConfig = $this->crondConfig->attr('http_server');
            if ($httpConfig['switch'] === true) {
                $httpServer = \Http\Server::createHttpServer();
                $socket = new \React\Socket\Server($httpConfig['port'], $loop);
                $httpServer->listen($socket);
            }
            //主进程定时器
            $this->running = true;
            $loop->addPeriodicTimer(1, function (\React\EventLoop\Timer\Timer $timer) {
                list($execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek) = \explode(' ', date("s i H d m w"));
                //执行及具体任务
                $taskList = $this->taskList->findTask($execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek);
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
                                $this->logger->info($task->getTaskName() . "killed by single kill pervious, and exit code :{$exitCode}");
                            } else {
                                $this->logger->info($task->getTaskName() . " is running(pid={$pid})");
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
                    $this->logger->info($task->getTaskName() . "[{$processCommand}] start");
                    $crondTaskMain->markProcess($taskUniqName, $process);
                }
                //执行具体任务结束
                //信号处理
                if (PHP_OS !== 'WINNT') {
                    pcntl_signal_dispatch();
                }
                //信号处理结束
                if (!$this->alive()) {
                    $loop->cancelTimer($timer);
                    $loop->stop();
                }
            });
            //执行主循环
            $loop->run();

            //主进程循环执行任务结束
            //等待所有子进程结束，结束进程
            while ($this->isTasksAlive()) {
                sleep(1);
            }
        } catch (\Exception $ex) {
            $this->logger->error($ex->getMessage());
        } catch (\Throwable $ex) {
            $this->logger->error($ex->getMessage());
        } finally {
            $this->logger->info('php_crond end');
        }
    }

    /**
     * 设置日志logger
     * @param Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
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