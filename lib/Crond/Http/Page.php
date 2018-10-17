<?php
namespace Crond\Http;

use Crond\Task\Main;
use Crond\Task\Config;

class Page extends Controller implements IPage
{
    /**
     * 查询服务状态
     * @return string
     */
    public function index()
    {
        $pid = Main::getPid();
        $taskList =  Config::getTaskList();
        $taskListStr = "";
        foreach ($taskList as $taskName => $task) {
            $params = implode(' ', $task['params']);
            $taskListStr .= "<tr><td>{$taskName}</td><td>{$task['daemon']}</td><td>{$task['filename']}</td><td>{$params}</td></tr>";
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
<div>进程ID：{$pid}</div>
<div>任务列表：</div>
<table>
<tbody>{$taskListStr}</tbody>
<thead><td>Name</td><td>Daemon</td><td>Filename</td><td>Params</td></thead>
</table>
</body>
</html>
DATA;
        return $data;
    }
}