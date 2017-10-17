<?php
namespace Crond\Http;

use Crond\Config;
use React\Http\Server;
use React\Http\Response;
use Psr\Http\Message\ServerRequestInterface;

class Main
{
    /**
     * http服务启动
     * @return void
     */
    public static function start()
    {
        $httpServerConfig = Config::attr('http_server');

        $loop = \React\EventLoop\Factory::create();

        $server = new Server(function (ServerRequestInterface $request) {
            $getParams = $request->getQueryParams();
            $c = ucfirst(isset($getParams['c']) ? $getParams['c'] : 'page');
            $a = isset($getParams['a']) ? $getParams['a'] : 'index';
            $className = "\\Crond\Http\\{$c}";

            if (class_exists($className) && method_exists($className, $a)) {
                try {
                    $controller = new $className($request);
                    $data = $controller->$a();
                    return self::render($data, $controller, 200, 'done!');
                } catch (\RuntimeException $ex) {
                    return self::render(null, $controller, 500, $ex->getMessage());
                }
            } else {
                return self::render(null, $controller, 404, "method[{$c}-{$a}] is not exists!");
            }
        });

        $socket = new \React\Socket\Server($httpServerConfig['port'], $loop);
        $server->listen($socket);

        $loop->run();
    }

    /**
     * 渲染控制器输出
     * @param mixed $output
     * @param Controller $controller
     * @param number $httpStatus
     * @param string $message
     */
    private static function render($output, $controller, $httpStatus = 200, $message = '')
    {
        if ($controller instanceof IPage) {
            return new Response(
                $httpStatus, ['Content-Type' => 'text/html'], $output);
        } else {
            return new Response(
                $httpStatus, ['Content-Type' => 'text/json'], json_encode([
                    'code' => $httpStatus,
                    'msg' => $message,
                    'data' => $output,
                ]));
        }
    }
}