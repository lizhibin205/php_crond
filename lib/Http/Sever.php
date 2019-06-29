<?php
namespace Http;

class Server
{
    /**
     * 创建一个HttpServer
     */
    public static function createHttpServer()
    {
        $httpServer = \React\Http\Server(function (ServerRequestInterface $request) {
            $getParams = $request->getQueryParams();
            $controller = ucfirst(isset($getParams['c']) ? $getParams['c'] : 'page');
            $action = isset($getParams['a']) ? $getParams['a'] : 'index';
            $className = "\\Http\\{$controller}";
            if (class_exists($className) && method_exists($className, $action)) {
                try {
                    $controller = new $className($request);
                    return $controller->$a();
                } catch (\Exception $ex) {
                    return Render::html(500, [], $ex->getMessage());
                }
            } else {
                //找不到方法
                return Render::html(404, [], "Controller:{$controller} and action:{$action} not found.");
            }
        });
    }
}