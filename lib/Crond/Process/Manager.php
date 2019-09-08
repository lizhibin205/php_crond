<?php
namespace Crond\Process;

class Manager
{
    /**
     * 子进程包装类列表
     */
    private $processWapperList = [];

    /**
     * 通过任务名称，查询子进程pid
     */
    public function getProcessPidByUniqName($taskUniqName)
    {

    }

    public function addWapper(ProcessWapper $wapper, $wapperName)
    {

    }
}