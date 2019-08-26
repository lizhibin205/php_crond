<?php
namespace Crond;

use Symfony\Component\Process\Process;
use React\EventLoop\Factory;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
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
        $taskList->loadTasks();
        //日志记录器
        $logger = new Logger('crond');
        $logger->pushHandler(new StreamHandler($crondConfig->attr('log_file'), Logger::INFO));

        //初始化Crond实例
        $crond = new Crond($crondConfig, $taskList);

        //注册信号函数
        //用于安全关闭任务-USR1
        if (PHP_OS !== 'WINNT') {
            Signal::register(SIGUSR1, function($signal) use($crond) {
                echo "please wait, shuting down the crond...", PHP_EOL;
                $crond->shutdown();
            });
            //用户重载配置文件-USR2
            Signal::register(SIGUSR2, function($signal) use($crond) {
                echo "reload task config...", PHP_EOL;
                $crond->reloadTask();
            });
            //接收子进程结束的信号
            Signal::register(SIGCHLD, function($signal) use($crond) {
                $crond->waitProcess();
            });
        }

        $crond->setLogger($logger);
        $crond->run();
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
            $this->logger->info("php_crond start LoopInterface with ". get_class($loop));
            //HTTP启动
            $httpConfig = $this->crondConfig->attr('http_server');
            if ($httpConfig['switch'] === true) {
                $httpServer = \Http\Server::createHttpServer($this);
                $socket = new \React\Socket\Server($httpConfig['port'], $loop);
                $httpServer->listen($socket);
            }
            //主进程定时器
            $this->running = true;
            $loop->addPeriodicTimer(1, function (\React\EventLoop\Timer\Timer $timer) use($loop) {
                list($execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek) = \explode(' ', date("s i H d m w"));
                //执行及具体任务
                $matchTaskList = $this->taskList->findTask($execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek);
                foreach ($matchTaskList as $task) {
                    //获取任务的唯一名称
                    $taskUniqName = $task->getUniqTaskName();
                    //判断是否single的任务
                    if ($task->isSingle()) {
                        $pid = $this->getProcessPidByUniqName($taskUniqName);
                        if ($pid > 0) {
                            //跳过该任务
                            $this->logger->info($task->getTaskName() . " is running(pid={$pid})");
                            continue;
                        }
                    }
                    //执行任务
                    $processCommand = $task->getExecution();
                    $process = new Process($processCommand);
                    $process->start(function ($type, $buffer) use($task, $process) {
                        //这个回调可能会被多次调用
                        //如果任务没有输出，则不会被触发
                        $outputFileName = $type === Process::ERR ? $task->getErrorOutput() : $task->getStandardOuput();
                        if (!empty($outputFileName) || is_writable($outputFileName)) {
                            file_put_contents($outputFileName, $buffer, FILE_APPEND);
                        } else {
                            $this->logger->error("Task " .$task->getTaskName() . " output is not writable.");
                        }
                    });
                    $this->logger->info($task->getTaskName() . "[{$processCommand}] start.");
                    $this->markProcess($taskUniqName, $process);
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
            $this->logger->error($ex->getMessage(), $ex->getTrace());
        } catch (\Throwable $ex) {
            $this->logger->error($ex->getMessage(), $ex->getTrace());
        } finally {
            $this->logger->info('php_crond end');
        }
    }

    /**
     * 关闭crond
     */
    public function shutdown()
    {
        $this->logger->info('php_crond shuwdown has been called.');
        $this->running = false;
    }

    /**
     * 重载任务列表
     */
    public function reloadTask()
    {
        $this->logger->info('php_crond reloadTask has been called.');
        $this->taskList->reloadTasks();
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
     * 返回主进程PID
     * @return number
     */
    public function getPid()
    {
        return getmypid();
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
     * 获取正在运行的任务
     */
    public function getRunningTasks()
    {
        $tasks = [];
        foreach ($this->processList as $process) {
            $pid = $process->getPid();
            if ($pid > 0) {
                $tasks[] = [
                    'pid' => $pid,
                    'command' => $process->getCommandLine(),
                ];
            }
        }
        return $tasks;
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
            throw new CrondRuntimeException("pid file is exists, check the crond php is running or not!");
        }
        $pid = \getmypid();
        if (!\file_put_contents($pidFileName, $pid)) {
            throw new CrondRuntimeException("counldn't create pid file!");
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
     * 处理子进程发送的SIGCHLD，防止僵尸进程
     * @return void
     */
    private function waitProcess()
    {
        foreach ($this->processList as $taskUniqName => $process) {
            if ($process->isTerminated()) {
                $this->logger->info($taskUniqName . " is terminated.");
                unset($this->processList[$taskUniqName]);
            }
        }
    }
}