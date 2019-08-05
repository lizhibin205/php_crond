<?php
namespace Http\Controller;

use Crond\Crond;
use Psr\Http\Message\ServerRequestInterface;

abstract class BaseController
{
    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var Crond
     */
    protected $crond;

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    public function setCrond(Crond $crond)
    {
        $this->crond = $crond;
    }
}