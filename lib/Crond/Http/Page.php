<?php
namespace Crond\Http;

class Page extends Controller implements IPage
{
    public function index()
    {
        return file_get_contents(PROJECT_ROOT . "/public/index.html");
    }
}