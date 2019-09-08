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
        $this->process = new Process(processCommand);
        $$this->process->start(function ($type, $buffer) {
            //这个回调可能会被多次调用
            //如果任务没有输出，则不会被触发
            $outputFileName = $type === Process::ERR ? $this->task->getErrorOutput() : $this->task->getStandardOuput();
            if (!empty($outputFileName) || is_writable($outputFileName)) {
                file_put_contents($outputFileName, $buffer, FILE_APPEND);
            }
        });
    }
}