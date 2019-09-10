<?php
namespace Crond\Process;

use Symfony\Component\Process\Process;
use Storage\Task;

/**
 * 子进程包装类
 */
class ProcessWapper
{
    /**
     * Task
     */
    private $task;
    /**
     * @var Process
     */
    private $process;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * 启动子进程
     */
    public function start()
    {
        $processCommand = $this->task->getExecution();
        $this->process = new Process($processCommand);
        $this->process->start(function ($type, $buffer) {
            //这个回调可能会被多次调用
            //如果任务没有输出，则不会被触发
            $outputFileName = $type === Process::ERR ? $this->task->getErrorOutput() : $this->task->getStandardOuput();
            if (!empty($outputFileName) || is_writable($outputFileName)) {
                file_put_contents($outputFileName, $buffer, FILE_APPEND);
            }
        });
    }

    /**
     * 返回子进程Pid
     * @return int
     */
    public function getPid()
    {
        $pid = $this->process->getPid();
        return is_numeric($pid) ? $pid : 0;
    }

    /**
     * 子进程是否已经终止
     * @return boolean
     */
    public function isTerminated()
    {
        return $this->process->isTerminated();
    }

    /**
     * 返回子进程终止代码
     * @return int
     */
    public function getExitCode()
    {
        return $this->process->getExitCode();
    }

    /**
     * 返回子进程终止错误信息
     * @return string
     */
    public function getExitCodeText()
    {
        return $this->process->getExitCodeText();
    }

    /**
     * 子进程是否成功执行
     * @return boolean
     */
    public function isSuccessful()
    {
        return $this->process->isSuccessful();
    }

    /**
     * 子进程是否在执行
     * @return boolean
     */
    public function isRunning()
    {
        return $this->process->isRunning();
    }

    /**
     * 获取执行脚本
     * @return string
     */
    public function getCommandLine()
    {
        return $this->process->getCommandLine();
    }
}