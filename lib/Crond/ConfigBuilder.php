<?php
namespace Crond;

use function Composer\Autoload\includeFile;

class ConfigBuiler
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
     * @return ConfigBuiler
     */
    public static function readConfigFromFile() : ConfigBuiler
    {
        $configFileData = include PROJECT_ROOT . "/config/base.php";
        $configBuiler = new ConfigBuiler();
        $configBuiler->model($configFileData['model'])
            ->logFile($configFileData['log_file'])->pidFile($configFileData['pid_file'])
            ->httpSwitch($configFileData['http_switch'])
            ->httpListen($configFileData['http_listen'])
            ->httpPort($configFileData['http_port'])
            ->httpLog($configFileData['http_log']);
        return $configBuiler;
    }

    public function model(string $model) : ConfigBuiler
    {
        $this->model = $model;
        return $this;
    }

    public function logFile(string $logFile) : ConfigBuiler
    {
        $this->logFile = $logFile;
        return $this;
    }

    public function pidFile(string $pidFile) : ConfigBuiler
    {
        $this->pidFile = $pidFile;
        return $this;
    }

    public function httpSwitch(bool $httpSwitch) : ConfigBuiler
    {
        $this->httpSwitch = $httpSwitch;
        return $this;
    }

    public function httpListen(string $httpListen) : ConfigBuiler
    {
        $this->httpListen = $httpListen;
        return $this;
    }

    public function httpPort(int $httpPort) : ConfigBuiler
    {
        $this->httpPort = $httpPort;
        return $this;
    }

    public function httpLog(string $httpLog) : ConfigBuiler
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
        return $config;
    }
}