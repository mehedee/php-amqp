<?php

namespace MH83\Amqp;

use Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class Core
{
    public $host;
    public $port;
    public $user;
    public $password;
    public $vhost;

    public $connection;
    public $channel;

    public function __construct($config = null, $exchange_name = '', $routingKey = null)
    {
        $this->host     = $config ? $config['host'] : getenv('RABBITMQ_HOST');
        $this->user     = $config ? $config['user'] : getenv('RABBITMQ_USER');
        $this->password = $config ? $config['password'] : getenv('RABBITMQ_PASSWORD');
        $this->vhost    = $config ? $config['vhost'] : getenv('RABBITMQ_VHOST');
        $this->port     = $config && isset($config['port']) ? $config['port'] : getenv('RABBITMQ_PORT') ?? 5672;

        $this->connection = new AMQPStreamConnection($this->host, $this->port, $this->user, $this->password, $this->vhost);
        $this->channel    = $this->connection->channel();
    }

    /**
     * @throws Exception
     */
    protected function shutdown()
    {
        $this->connection->close();
    }
}
