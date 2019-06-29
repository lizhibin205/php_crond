<?php
namespace Http;

use React\Http\Response;

class Render
{
    /**
     * 渲染一个json数据
     * @param int $httpStatus HTTP状态码
     * @param array $headerList Http header
     * @param int $jsonCode  Json状态码
     * @param string $jsonMessage  Json 信息
     * @param mixed $jsonData  Json数据
     * @return \React\Http\Response
     */
    public static function json($httpStatus, $headerList, $jsonCode, $jsonMessage, $jsonData)
    {
        $data = json_encode([
            'code'    => $jsonCode,
            'message' => $jsonMessage,
            'data'    => $jsonData,
        ]);
        if (!isset($headerList['Content-Type'])) {
            $headerList['Content-Type'] = 'text/json';
        }
        return new Response($httpStatus, $headerList, $data);
    }

    /**
     * 渲染一个html页面
     * @param int $httpStatus HTTP状态码
     * @param array $headerList Http header
     * @param string $html  html页面数据
     * @return \React\Http\Response
     */
    public static function html($httpStatus, $headerList, $html)
    {
        if (!isset($headerList['Content-Type'])) {
            $headerList['Content-Type'] = 'text/html';
        }
        return new Response($httpStatus, $headerList, $html);
    }
}