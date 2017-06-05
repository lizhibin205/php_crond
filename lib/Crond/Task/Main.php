<?php
namespace Crond\Task;

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
    private $statusList = [];

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
        $crondTaskMain->createPidFile();
        //日志记录器
        $crondConfig = \Crond\Config::getConfig('base');
        $logger = new \Monolog\Logger('crond');
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($crondConfig['log_file'], \Monolog\Logger::INFO));

        //程序开始记录日志
        $logger->info("php_crond start");
        //主进程循环执行任务
        $loop = \React\EventLoop\Factory::create();
        $loop->addPeriodicTimer(1, function($timer) use ($crondTaskMain, $logger, $loop){
            list($execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek) = \explode(' ', date("s i H d m w"));
            //执行及具体任务
            $taskList = Config::find($execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek);
            foreach ($taskList as $task) {
                if ($task->isSingle() && $crondTaskMain->checkTaskExists($task->getTaskName()) === Main::TASK_EXEC) {
                    $logger->info($task->getTaskName() . "is running");
                    continue;
                }

                //fork进程，执行任务
                $childPid = \pcntl_fork();
                if ($childPid == -1) {
                    throw new \RuntimeException("Can't fork child process!");
                }
                //子进程执行任务
                if ($childPid == 0) {
                    list($filename, $params) = $task->getExec();
                    \pcntl_exec($filename, $params);
                } else {
                    //父进程标识进程执行状态
                    $logger->info("start task " . $task->getTaskName());
                    $crondTaskMain->markTask($task->getTaskName(), $childPid);
                }
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
        while (count($crondTaskMain->statusList) > 0) {
            foreach ($crondTaskMain->statusList as $taskName => $taskInfo) {
                if ($crondTaskMain->checkTaskExists($taskName) === self::TASK_EXEC) {
                    break;
                }
            }
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
     * 创建pid文件
     * @return void
     */
    private function createPidFile()
    {
        $pidFileName = PROJECT_ROOT . "/logs/crond.pid";
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
     * @param string $taskName 任务名称
     * @param int $childPid 进程ID
     */
    private function markTask($taskName, $childPid)
    {
        $this->statusList[$taskName] = [
            'pid' => $childPid,
            'exec_time' => time()
        ];
    }

    /**
     * 检查任务执行状态
     * @param string $taskName 任务名称
     * @return int 任务状态
     */
    private function checkTaskExists($taskName)
    {
        $taskInfo = isset($this->statusList[$taskName]) ? $this->statusList[$taskName] : null;
        if (\is_null($taskInfo)) {
            return self::TASK_NONE;
        }

        $childSignal = \pcntl_waitpid($taskInfo['pid'], $status, WNOHANG);
        if ($childSignal == -1 || \pcntl_wifexited ($status)) {
            unset($this->statusList[$taskName]);
            return self::TASK_NONE;
        } else {
            return self::TASK_EXEC;
        }
    }
}