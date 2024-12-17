<?php

namespace MH83\Amqp;

use Exception;
use PhpAmqpLib\Message\AMQPMessage;

class Publisher extends Core
{
    public $queue_name;
    public $exchange_name;
    public $routingKey;

    public function __construct($queue_name, $config = null, $exchange_name = '', $routingKey = null)
    {
        $this->queue_name    = $queue_name;
        $this->exchange_name = $exchange_name;
        $this->routingKey    = $routingKey;

        parent::__construct($config);
    }

    /**
     * @throws Exception
     */
    public function publish($message, $durable = true) : bool
    {
        try {
            if (!$this->connection->isConnected()) {
                $this->connection->reconnect();
            }
            $this->channel->exchange_declare($this->exchange_name, 'direct', false, $durable, false);
            $this->channel->queue_declare($this->queue_name, false, $durable, false, false);
            $this->channel->queue_bind($this->queue_name, $this->exchange_name, $this->routingKey);
            $messageType = gettype($message);
            if ($messageType == 'array' || $messageType == 'object') {
                $message = json_encode($message);
            }

            $this->channel->basic_publish(new AMQPMessage($message), $this->exchange_name, $this->routingKey ?? $this->queue_name);
            $this->shutdown();
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }


}
