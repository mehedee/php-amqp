<?php

namespace MH83\Amqp;

use Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Queue
{
    public $queue_name;
    public $exchange_name;
    public $routingKey;
    public $host;
    public $port;
    public $user;
    public $password;
    public $vhost;

    public $connection;
    public $channel;


    public function __construct($queue_name, $config = null, $exchange_name = '', $routingKey = null)
    {
        $this->queue_name    = $queue_name;
        $this->exchange_name = $exchange_name;
        $this->routingKey    = $routingKey;
        $this->host          = $config ? $config['host'] : getenv('RABBITMQ_HOST');
        $this->user          = $config ? $config['user'] : getenv('RABBITMQ_USER');
        $this->password      = $config ? $config['password'] : getenv('RABBITMQ_PASSWORD');
        $this->vhost         = $config ? $config['vhost'] : getenv('RABBITMQ_VHOST');
        $this->port          = $config && isset($config['port']) ? $config['port'] : getenv('RABBITMQ_PORT') ?? 5672;

        $this->connection = new AMQPStreamConnection($this->host, $this->port, $this->user, $this->password, $this->vhost);
        $this->channel    = $this->connection->channel();
    }

    /**
     * @throws Exception
     */
    public function publish($message, $durable = true) : bool
    {
        try {
            $this->channel->queue_declare($this->queue_name, false, $durable, false, false);
            $messageType = gettype($message);
            if ($messageType == 'array' || $messageType == 'object') {
                $message = json_encode($message);
            }

            $this->channel->basic_publish(new AMQPMessage($message), $this->exchange_name, $this->routingKey ?? $this->queue_name);
//            $this->shutdown();
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function consume($callback, $durable = true, $noAck = false)
    {
        try {
            $this->channel->queue_declare($this->queue_name, false, $durable, false, false);
            $this->channel->basic_consume($this->queue_name, '', false, $noAck, false, false, $callback);
            while ($this->channel->is_consuming()) {
                $this->channel->wait();
            }

            $this->shutdown();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    private function shutdown()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
