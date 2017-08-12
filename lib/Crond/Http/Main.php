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
            $c = ucfirst(isset($getParams['c']) ? $getParams['c'] : 'status');
            $a = isset($getParams['a']) ? $getParams['a'] : 'index';
            $className = "\\Crond\Http\\{$c}";

            if (class_exists($className) && method_exists($className, $a)) {
                try {
                    $data = (new $className($request))->$a();
                    $output = json_encode([
                        'code' => 200,
                        'msg' => 'done',
                        'data' => $data,
                    ]);
                } catch (\RuntimeException $ex) {
                    $output = json_encode([
                        'code' => $ex->getCode(),
                        'msg' => $ex->getMessage(),
                        'data' => null,
                    ]);
                }
            } else {
                $output = json_encode([
                    'code' => 404,
                    'msg' => "method[{$c}-{$a}] is not exists!",
                    'data' => null,
                ]);
            }

            return new Response(
                200,
                array('Content-Type' => 'text/plain'),
                $output
                );
        });

        $socket = new \React\Socket\Server($httpServerConfig['port'], $loop);
        $server->listen($socket);

        $loop->run();
    }
}