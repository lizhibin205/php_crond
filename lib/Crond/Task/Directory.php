<?php
namespace Crond\Task;

class Directory
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
        try {
            $dirIterator = new \DirectoryIterator($directory);

            foreach ($dirIterator as $file) {
                if (in_array($file->getFilename(), ['.', '..'])) {
                    continue;
                }

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
        } catch (\Exception $ex) {
            trigger_error($ex->getMessage(), E_USER_WARNING);
        }

        return $tasksList;
    }
}