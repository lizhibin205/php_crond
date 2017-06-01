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
    private static $configData = [];

    /**
     * 获取配置文件中的内容
     * @param string $name
     * @return mixed|null 返回配置文件的信息，如果文件不存在，则返回null
     */
    public static function getConfig($name)
    {
        if (isset(self::$configData[$name])) {
            return self::$configData[$name];
        }
        $filename = PROJECT_ROOT . "/config/{$name}.php";
        if (is_file($filename)) {
            return self::$configData[$name] = include $filename;
        } else {
            return null;
        }
    }
}