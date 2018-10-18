<?php
namespace Crond\Http;

use React\Http\Response;

class Output
{
    public static function render($output, $controller, $httpStatus = 200, $message = '')
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