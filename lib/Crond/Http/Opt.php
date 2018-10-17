<?php
namespace Crond\Http;

final class Opt extends Controller
{
    /**
     * 安全关闭php_crond服务
     * return array
     */
    public function shutdown()
    {
        \Crond\Task\Main::shutdown();
        return [
            'code' => 1,
            'msg' => 'shutdown!'
        ];
    }

    /**
     * 重新加载任务列表
     * @return array
     */
    public function reload()
    {
        \Crond\Task\Main::reloadTask();
        return [
            'code' => 1,
            'msg' => 'reload!'
        ];
    }

    /**
     * 添加计划任务
     * @deprecated
     * @return array
     */
    public function add()
    {
        throw new \Exception("deprecated api");
    }

    /**
     * 移除任务
     * @deprecated
     * @return array
     */
    public function remove()
    {
        throw new \Exception("deprecated api");
    }
}