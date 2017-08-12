<?php
namespace Crond\Http;

use Psr\Http\Message\ServerRequestInterface;

abstract class Controller
{
    /**
     * 请求对象
     * @var ServerRequestInterface
     */
    protected $request;

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }
}