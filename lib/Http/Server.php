<?php
namespace Http;

use Crond\Crond;
use Psr\Http\Message\ServerRequestInterface;

class Server
{
    /**
     * 创建一个HttpServer
     */
    public static function createHttpServer(Crond $crond)
    {
        $httpServer = new \React\Http\Server(function (ServerRequestInterface $request) use ($crond) {
            $getParams = $request->getQueryParams();
            $controller = ucfirst(isset($getParams['c']) ? $getParams['c'] : 'Page');
            $action = isset($getParams['a']) ? $getParams['a'] : 'index';
            $className = "\\Http\\Controller\\{$controller}";
            if (class_exists($className) && method_exists($className, $action)) {
                try {
                    $controllerClass = new $className($request);
                    return $controllerClass->$action();
                } catch (\Exception $ex) {
                    return Render::html(500, [], $ex->getMessage());
                } catch (\Throwable $ex) {
                    return Render::html(500, [], $ex->getMessage());
                }
            } else {
                //找不到方法
                return Render::html(404, [], "Controller:{$controller} and action:{$action} not found.");
            }
        });
        return $httpServer;
    }
}