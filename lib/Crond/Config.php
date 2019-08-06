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
     * Crond服务配置数据
     * @var array
     */
    private $configData = [];

    public function __construct()
    {
        $filename = PROJECT_ROOT . "/config/base.php";
        if (is_file($filename)) {
            $this->configData = include $filename;
        } else {
            throw new CrondRuntimeException("php_crond base config file not exists!");
        }
    }

    /**
     * 获取php_crond配置
     * @param string $name 属性名
     */
    public function attr($name)
    {
        $configData = $this->configData;
        if (isset($configData[$name])) {
            return $configData[$name];
        } else {
            throw new CrondRuntimeException("php_crond base config[{$name}] not exists!");
        }
    }
}