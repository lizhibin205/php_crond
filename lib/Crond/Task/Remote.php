<?php 
namespace Crond\Task;

class Remote
{
    /**
     * 调用远程接口，获取任务列表
     * @param string $url
     * @param string $serverId
     */
    public static function registerTask($url, $serverId)
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
