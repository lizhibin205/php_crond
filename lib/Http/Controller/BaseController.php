<?php
namespace Http\Controller;

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