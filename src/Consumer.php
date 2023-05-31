<?php

namespace MH83\Amqp;

use Exception;

class Consumer extends Core
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
    public function consume($callback, $durable = true, $noAck = false)
    {
        try {
            if (!$this->connection->isConnected()) {
                $this->connection->reconnect();
            }

            $this->channel->queue_declare($this->queue_name, false, $durable, false, false);
            $this->channel->basic_qos(null, 10, null);

            $this->channel->basic_consume($this->queue_name, '', false, $noAck, false, false, $callback);
            while ($this->channel->is_consuming()) {
                $this->channel->wait();
            }

            $this->shutdown();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
