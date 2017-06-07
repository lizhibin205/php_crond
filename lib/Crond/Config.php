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
     * 数据配置
     * @var array
     */
    private static $configData = null;

    /**
     * 获取php_crond配置
     * @param string $name 属性名
     */
    public static function attr($name)
    {
        if (is_null(self::$configData)) {
            self::$configData = self::getConfig();
        }
        if (isset(self::$configData[$name])) {
            return self::$configData[$name];
        } else {
            throw new \RuntimeException("php_crond base config[{$name}] not exists!");
        }
    }

    /**
     * 获取配置文件中的内容
     * @throws \RuntimeException
     * @return void
     */
    private static function getConfig()
    {
        $filename = PROJECT_ROOT . "/config/base.php";
        if (is_file($filename)) {
            return include $filename;
        } else {
            throw new \RuntimeException("php_crond base config file not exists!");
        }
    }
}