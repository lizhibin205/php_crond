<?php
namespace Http\Controller;

use Psr\Http\Message\ServerRequestInterface;

abstract class BaseController
{
    /**
     * 
     * @var ServerRequestInterface
     */
    protected  $request;

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }
}