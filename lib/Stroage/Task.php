<?php
namespace Stroage;

class Task
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * 创建一个Task对象
     * @param array $data
     * @return Task
     */
    public static function create(array $data)
    {
        return new Task($data);
    }
}