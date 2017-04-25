<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('rabbitmq.achilles.systems', 5672, 'admin', 'admin');
$channel = $connection->channel();

$queue_name = "rabbitmq-presentation";
$channel->queue_declare($queue_name, false, false, false, false);

echo ' [*] Waiting for chat msgs. To exit press CTRL+C', "\n";

$callback = function($msg){
    echo ' [x] ', $msg->body, "\n";

    $myfile = 'chat_log.txt';
    $handle = fopen($myfile, 'a') or die('Cannot open file:  '.$myfile);
    fwrite($handle, $msg->body."\n");
    fclose($handle);
};

$channel->basic_consume($queue_name, '', false, true, false, false, $callback);

while(count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();

