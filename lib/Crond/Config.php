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
    private $httpSwitch;
    private $httpListen;
    private $httpPort;
    private $httpLog;

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
    public function getHttpSwitch() : bool
    {
        return $this->httpSwitch;
    }
    public function setHttpSwitch(bool $httpSwitch)
    {
        $this->httpSwitch = $httpSwitch;
    }
    public function getHttpListen() : string
    {
        return $this->httpListen;
    }
    public function setHttpListen(string $httpListen)
    {
        $this->httpListen = $httpListen;
    }
    public function getHttpPort() : int
    {
        return $this->httpPort;
    }
    public function setHttpPort(int $httpPort)
    {
        $this->httpPort = $httpPort;
    }
    public function getHttpLog() : string
    {
        return $this->httpLog;
    }
    public function setHttpLog(string $httpLog)
    {
        $this->httpLog = $httpLog;
    }
}