<?php
namespace Http\Controller;

use Http\Render;

final class Console extends BaseController
{
    /**
     * 关闭Crond
     * @return \React\Http\Message\Response
     */
    public function shutdown()
    {
        $this->crond->shutdown();
        return Render::json(200, [], 200, 'success', null);
    }

    /**
     * 重新加载任务配置
     * @return \React\Http\Message\Response
     */
    public function reloadTasks()
    {
        $this->crond->reloadTask();
        return Render::json(200, [], 200, 'success', null);
    }
}