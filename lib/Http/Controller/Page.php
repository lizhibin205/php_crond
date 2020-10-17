<?php
namespace Http\Controller;

use Http\Render;

final class Page extends BaseController
{
    /**
     * php_crond状态
     * @return \React\Http\Message\Response
     */
    public function index()
    {
        $jsonData = [
            'pid' => $this->crond->getPid(),
            'status' => $this->crond->alive() ? '运行' : '停止',
            'tasks' => $this->crond->getAllTasks(),
            'running_tasks' => $this->crond->getRunningTasks(),
        ];
        return Render::json(200, [], 200, 'success', $jsonData);
    }
}