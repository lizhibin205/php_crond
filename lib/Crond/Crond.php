<?php
namespace Crond;

use React\EventLoop\Factory;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Storage\TaskManager;
use Crond\Exception\CrondRuntimeException;
use Crond\Process\Manager;
use Crond\Process\ProcessWapper;

class Crond
{
    /**
     * 计划任务执行周期
     * @var integer
     */
    const PERIODIC = 1;

    /**
     * 等待子进程结束间隔时间，默认1s
     * @var integer
     */
    const SHUTDOWN_WAIT_MICRO_SECOND = 1000000;

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
     * @var TaskManager
     */
    private $taskManager;

    /**
     * 日志
     * @var Logger
     */
    private $logger;

    /**
     * 子进程管理器
     * @var Manager
     */
    private $processManager;

    /**
     * Crond
     */
    public function __construct(Config $crondConfig, TaskManager $taskManager)
    {
        $this->crondConfig = $crondConfig;
        $this->taskManager = $taskManager;
    }

    /**
     * 启动定时任务
     * @return void
     */
    public static function start()
    {
        //获取Crond启动配置
        $crondConfig = ConfigBuilder::readConfigFromFile()
            ->build();
        //日志记录器
        $logger = new Logger('crond');
        $logger->pushHandler(new StreamHandler($crondConfig->getLogFile(), Logger::INFO));

        //初始化Crond实例
        $logger->info("create crond...");
        $crond = new Crond($crondConfig, (new TaskManager())->loadTasks());
        $crond->setLogger($logger);
        $crond->setProcessManager(new Manager());
        //创建PID文件
        $crond->createPidFile($crondConfig->getPidFile());

        //注册信号函数
        //用于安全关闭任务-USR1
        $logger->info("register crond signal...");
        Signal::registerAll($crond);

        //启动，核心循环
        $crond->run();
    }

    /**
     * 执行任务主循环
     */
    public function run()
    {
        try {
            //程序开始记录日志
            $this->logger->info("php_crond start");
            //主进程循环执行任务
            $loop = Factory::create();
            $this->logger->info("php_crond start LoopInterface with ". get_class($loop));
            //HTTP启动
            if ($this->crondConfig->getHttpSwitch()) {
                $httpServer = \Http\Server::createHttpServer($loop, $this);
                $socket = new \React\Socket\Server($this->crondConfig->getHttpPort(), $loop);
                $httpServer->listen($socket);
            }
            //主进程定时器
            $this->running = true;
            $loop->addPeriodicTimer(self::PERIODIC, function (\React\EventLoop\Timer\Timer $timer) use($loop) {
                list($execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek) = \explode(' ', date("s i H d m w"));
                //执行及具体任务
                $matchTaskList = $this->taskManager->findTask($execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek);
                foreach ($matchTaskList as $task) {
                    //获取任务的唯一名称
                    $taskUniqName = $task->getNowTaskName();
                    //判断是否single的任务
                    if ($task->isSingle()) {
                        $pid = $this->processManager->getProcessPidByUniqName($taskUniqName);
                        if ($pid > 0) {
                            //跳过该任务
                            $this->logger->info($task->getTaskName() . " is running(pid={$pid})");
                            continue;
                        }
                    }
                    //执行任务
                    $processWapper = new ProcessWapper($task);
                    $processWapper->start();
                    $this->logger->info($task->getTaskName() . "[" . $task->getExecution()
                        . "] start.");
                    $this->processManager->addWapper($processWapper, $taskUniqName);
                }
                //执行具体任务结束
                //信号处理
                Signal::dispatch();
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
            while ($this->processManager->hasTasksAlive()) {
                usleep(self::SHUTDOWN_WAIT_MICRO_SECOND);
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
        $this->taskManager->reloadTasks();
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
     * 设置子进程管理器
     */
    public function setProcessManager(Manager $manager)
    {
        $this->processManager = $manager;
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
     * 获取正在运行的任务
     */
    public function getRunningTasks()
    {
        return $this->processManager->getRunningTasks();
    }

    /**
     * 创建pid文件
     * @param string $pidFileName pid文件路径
     * @throws \RuntimeException
     * @return void
     */
    public function createPidFile($pidFileName)
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
     * 处理子进程发送的SIGCHLD，防止僵尸进程
     * @return void
     */
    public function waitProcess()
    {
        $this->processManager->waitProcess($this->logger);
    }
}