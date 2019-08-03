<?php
namespace Stroage;

class Reader
{
    /**
     * 自动扫描注册目录下的任务文件
     * @param string $directory
     * @param string $keyPrefix
     * @return array
     */
    public static function registerTaskDirectory($directory, $keyPrefix = '')
    {
        $tasksList = [];

        //遍历注册目录下的所有目录
        $dirIterator = new \DirectoryIterator($directory);
        foreach ($dirIterator as $file) {
            if (in_array($file->getFilename(), ['.', '..'])) {
                continue;
            }
            //如果是目录，进行递归
            if ($file->isDir()) {
                $subDirectory = $file->getPath() . "/" . $file->getFilename();
                $tasksList = array_merge($tasksList, self::registerTaskDirectory($subDirectory, md5($subDirectory)));
            } else {
                //is file
                if ($file->getExtension() == 'php') {
                    $fileName = $file->getPath() . "/" . $file->getFilename();
                    $subTaskList = include $fileName;
                    //add key prefix
                    if (strlen($keyPrefix) > 0) {
                        $arrOldKeys = array_keys($subTaskList);
                        foreach ($arrOldKeys as $oldKey) {
                            $val = $subTaskList[$oldKey];
                            $subTaskList[$keyPrefix."-".$oldKey] = $val;
                            unset($subTaskList[$oldKey]);
                        }
                    }
                    $tasksList = array_merge($tasksList, $subTaskList);
                }
            }
        }

        return $tasksList;
    }

    /**
     * 调用远程接口，获取任务列表
     * @param string $url
     * @param string $serverId
     */
    public static function registerTaskRemote($url, $serverId)
    {
        $taskList = [];
        try {
            $client = new \GuzzleHttp\Client([
                'timeout' => 5,
            ]);
            $url = $url . "?" . http_build_query([
                'server_id' => $serverId,
                '_time'     => time(),
            ]);
            $res = $client->request('GET', $url);
            if ($res->getStatusCode() != 200) {
                throw new \Exception("request url[{$url}] failure.");
            }
            $returnTaskList = json_decode($res->getBody(), true);
            if (!is_array($returnTaskList)) {
                throw new \Exception("response data is not a array.");
            }
            foreach ($returnTaskList as $returnKey => $returnTask) {
                if (($result = Unit::checkTask($returnTask))) {
                    $taskList[$returnKey] = $result;
                }
            }
        } catch (\Exception $ex) {
            trigger_error($ex->getMessage(), E_USER_WARNING);
        } finally {
            return $taskList;
        }
    }
}