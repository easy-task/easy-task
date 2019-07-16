<?php
namespace EasyTask;

class Command
{
    /**
     * 通讯文件
     */
    private $msgFile;

    /**
     * 构造函数
     * @throws
     */
    public function __construct()
    {
        $this->msgFile = sprintf('/tmp/%s.txt', md5(__FILE__));
        if (!file_exists($this->msgFile))
        {
            if (!file_put_contents($this->msgFile, ''))
            {
                Console::error('创建通讯文件失败');
            }
        }
    }

    /**
     * 获取数据
     * @return array|mixed
     * @throws
     */
    public function get()
    {
        $content = @file_get_contents($this->msgFile);
        if (!$content)
        {
            return [];
        }
        $data = json_decode($content, true);
        return is_array($data) ? $data : [];
    }

    /**
     * 重置数据
     * @param $data
     */
    public function set($data)
    {
        file_put_contents($this->msgFile, json_encode($data));
    }

    /**
     * 投递数据
     * @param $command
     */
    public function push($command)
    {
        $data = $this->get();
        array_push($data, $command);
        $this->set($data);
    }

    /**
     * 发送命令
     * @param $command
     */
    public function send($command)
    {
        $command['time'] = time();
        $this->push($command);
    }

    /**
     * 接收命令(过期清空)
     * @param $msgType
     * @param $command
     */
    public function receive($msgType, &$command)
    {
        $data = $this->get();
        foreach ($data as $key => $item)
        {
            if ($item['msgType'] == $msgType)
            {
                $command = $item;
                unset($data[$key]);
                break;
            }
        }
        $this->set($data);
    }
}