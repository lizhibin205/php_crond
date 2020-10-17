<?php
namespace Crond;

use function Composer\Autoload\includeFile;

class ConfigBuilder
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

    /**
     * 创建配置
     * @return ConfigBuilder
     */
    public static function readConfigFromFile() : ConfigBuilder
    {
        $configFileData = include PROJECT_ROOT . "/config/base.php";
        $configBuiler = new ConfigBuilder();
        $configBuiler->model($configFileData['model'])
            ->logFile($configFileData['log_file'])->pidFile($configFileData['pid_file'])
            ->httpSwitch($configFileData['http_switch'])
            ->httpListen($configFileData['http_listen'])
            ->httpPort($configFileData['http_port'])
            ->httpLog($configFileData['http_log']);
        return $configBuiler;
    }

    public function model(string $model) : ConfigBuilder
    {
        $this->model = $model;
        return $this;
    }

    public function logFile(string $logFile) : ConfigBuilder
    {
        $this->logFile = $logFile;
        return $this;
    }

    public function pidFile(string $pidFile) : ConfigBuilder
    {
        $this->pidFile = $pidFile;
        return $this;
    }

    public function httpSwitch(bool $httpSwitch) : ConfigBuilder
    {
        $this->httpSwitch = $httpSwitch;
        return $this;
    }

    public function httpListen(string $httpListen) : ConfigBuilder
    {
        $this->httpListen = $httpListen;
        return $this;
    }

    public function httpPort(int $httpPort) : ConfigBuilder
    {
        $this->httpPort = $httpPort;
        return $this;
    }

    public function httpLog(string $httpLog) : ConfigBuilder
    {
        $this->httpLog = $httpLog;
        return $this;
    }

    /**
     * 创建Config对象
     * @return Config
     */
    public function build() : Config
    {
        $config = new Config();
        $config->setModel($this->model);
        $config->setLogFile($this->logFile);
        $config->setPidFile($this->pidFile);
        $config->setHttpSwitch($this->httpSwitch);
        $config->setHttpListen($this->httpListen);
        $config->setHttpPort($this->httpPort);
        $config->setHttpLog($this->httpLog);
        return $config;
    }
}