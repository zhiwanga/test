<?php

namespace app\controller\orders;

use app\BaseController;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Order extends BaseController
{
    public function create(){
        $conf = [
            'host' => '127.0.0.1',
            'port' => 5672,
            'user' => 'admin',
            'pwd' => '123123',
            'vhost' => '/',
        ];

        $exchangeName = 'exchange_test1'; //交换机名
        $queueName = 'queue_test1'; //队列名称
        $routingKey = ''; //路由关键字，直连不需要

        $conn = new AMQPStreamConnection( //建立生产者与mq之间的连接
            $conf['host'], $conf['port'], $conf['user'], $conf['pwd'], $conf['vhost']
        );
        $channel = $conn->channel(); //在已连接基础上建立生产者与mq之间的通道

        $channel->exchange_declare($exchangeName, 'direct', false, true, false); //声明初始化交换机
        $channel->queue_declare($queueName, false, true, false, false); //声明初始化一条队列
        $channel->queue_bind($queueName, $exchangeName, $routingKey); //将队列与某个交换机进行绑定，并使用路由关键字

        for ($i=1; $i<=20; $i++){
            $msgBody = json_encode(["key1" => 'test'.$i]);
            echo $msgBody .'<br />';
            $msg = new AMQPMessage($msgBody, ['content_type' => 'text/plain', 'delivery_mode' => 2]);   //构建消息
            $channel->basic_publish($msg, $exchangeName, $routingKey);     //发布消息到某个交换机
        }

        $channel->close();
        $conn->close();
    }
}