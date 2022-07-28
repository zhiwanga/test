<?php
declare (strict_types = 1);

namespace app\command;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class Consume extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('consume')
            ->setDescription('the consume command');
    }

    protected function execute(Input $input, Output $output)
    {
        $conf = [
            'host'      => '127.0.0.1',
            'port'      => 5672,
            'user'      => 'admin',
            'password'  => '123123',
            'vhost'     => '/',
        ];
        $exchangeName   = 'exchange_test1';   //交换机名
        $queueName      = 'queue_test1';      //队列名称
        $routingKey     = '';                 //路由关键字(也可以省略)
        
        //建立生产者与mq之间的连接
        $conn = new AMQPStreamConnection($conf['host'], $conf['port'], $conf['user'], $conf['password'], $conf['vhost']);
        $channel = $conn->channel();    //在已连接基础上建立生产者与mq之间的通道
        $channel->queue_declare($queueName, false, true, false, false);     //声明初始化一条队列

        //回调函数，数据处理
        $callback = function($msg) {
            echo " Received: ", $msg->body, "n";
        };
        $channel->basic_consume($queueName, '', false, true, false, false, $callback);      //消费接收消息
        
        //监听消息，一有消息，立马就处理
        while(count($channel->callbacks)) {
            $channel->wait();
        }
    }
}
