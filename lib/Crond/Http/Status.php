<?php
namespace Crond\Http;

class Status
{
    public function __construct(){}

    /**
     * 返回http服务状态
     * @return string 
     */
    public function index()
    {
        return "running";
    }

    /**
     * 获取task config的配置
     * @return mixed
     */
    public function getTaskConfig()
    {
        return include PROJECT_ROOT . "/config/task.php";
    }
}