<?php
namespace Crond\Process;

use Monolog\Logger;

class Manager
{
    /**
     * 子进程包装类列表
     */
    private $processWapperList = [];

    /**
     * 通过任务名称，查询子进程pid
     * @param string $wapperName
     */
    public function getProcessPidByUniqName($wapperName)
    {
        if (isset($this->processWapperList[$wapperName])) {
            $processWapper = $this->processWapperList[$wapperName];
            return $processWapper->getPid();
        }
        return 0;
    }

    /**
     * 添加自进程到管理器
     * @param ProcessWapper $wapper
     * @param string $wapperName
     */
    public function addWapper(ProcessWapper $wapper, $wapperName)
    {
        $this->processWapperList[$wapperName] = $wapper;
    }

    /**
     * 处理子进程发送的SIGCHLD，防止僵尸进程
     */
    public function waitProcess(Logger $logger)
    {
        foreach ($this->processWapperList as $wapperName => $wapper) {
            if ($wapper->isTerminated()) {
                $exitCode = $wapper->getExitCode();
                $exitMessage = $wapper->getExitCodeText();
                if ($wapper->isSuccessful()) {
                    $logger->info($wapperName . " is terminated, with exit code {$exitCode}({$exitMessage}).");
                } else {
                    $logger->warn($wapperName . " is terminated, with exit code {$exitCode}({$exitMessage}).");
                }
                unset($this->processList[$wapperName]);
            }
        }
    }

    /**
     * 检查是否有进程存活
     * @return boolean
     */
    public function hasTasksAlive()
    {
        foreach ($this->processWapperList as $wapper) {
            if ($wapper->isRunning()) {
                return true;
            }
        }
        return false;
    }

    /**
     * 返回正在执行的任务
     * @return array
     */
    public function getRunningTasks()
    {
        $tasks = [];
        foreach ($this->processWapperList as $wapper) {
            $pid = $wapper->getPid();
            if ($pid > 0) {
                $tasks[] = [
                    'pid' => $pid,
                    'command' => $wapper->getCommandLine(),
                ];
            }
        }
        return $tasks;
    }
}