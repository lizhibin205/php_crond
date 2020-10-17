<?php
namespace Crond;

/**
 * 获取配置参数
 * @author lizhibin
 *
 */
class Config
{
    /**
     * 模式
     * @var string
     */
    private $model;
    /**
     * 日志文件
     * @var string
     */
    private $logFile;
    /**
     * 进程文件
     * @var string
     */
    private $pidFile;

    public function getModel() : string
    {
        return $this->model;
    }
    public function setModel(string $model) : void
    {
        $this->model = $model;
    }
    public function getLogFile() : string
    {
        return $this->logFile;
    }
    public function setLogFile(string $logFile) : void
    {
        $this->logFile = $logFile;
    }
    public function getPidFile() : string
    {
        return $this->pidFile;
    }
    public function setPidFile(string $pidFile) : void
    {
        $this->pidFile = $pidFile;
    }
}