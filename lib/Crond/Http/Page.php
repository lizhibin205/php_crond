<?php
namespace Crond\Http;

use Crond\Task\Main;

class Page extends Controller implements IPage
{
    /**
     * 查询服务状态
     * @return string
     */
    public function index()
    {
        $pid = Main::getPid();
        $status = Main::getRunStatus() ? '运行' : '停止';
        $taskList =  Main::getTaskListStatus();
        $taskListStr = "";
        foreach ($taskList as $taskName => $task) {
            $params = implode(' ', $task['params']);
            $taskListStr .= "<tr><td>{$taskName}</td><td>{$task['daemon']}</td><td>{$task['filename']}</td><td>{$params}</td><td>{$task['pid']}</td></tr>";
        }

        $data = 
<<<DATA
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>php_crond control center</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<div>进程ID：{$pid}，运行状态：{$status}</div>
<div>任务列表：</div>
<table border="1" cellspacing="0" cellpadding="0">
<tbody>{$taskListStr}</tbody>
<thead><td>Name</td><td>Daemon</td><td>Filename</td><td>Params</td><td>Pid</td></thead>
</table>
</body>
</html>
DATA;
        return $data;
    }
}