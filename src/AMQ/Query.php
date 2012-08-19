<?php

namespace AMQ;

use AMQ\EventLoop;
use AMQ\Client;

use Evenement\EventEmitter;

class Query extends EventEmitter
{
    private $loop;

    private $client;

    private $connection;

    private $query;

    private $id;

    public function __construct(EventLoop $loop, Client $client, $query)
    {
        $this->loop   = $loop;
        $this->client = $client;
        $this->query  = $query;

        $loop->addQuery($this);
    }

    public function getId()
    {
        if (is_null($this->id)) {
            $this->id = spl_object_hash($this);
        }

        return $this->id;
    }

    public function getConnection()
    {
        $this->connect();

        return $this->connection;
    }

    public function getConnectionId()
    {
        $this->connect();

        return spl_object_hash($this->connection);
    }

    public function connect()
    {
        if (is_null($this->connection)) {
            $this->connection = $this->client->getConnection();
        }
    }

    public function isConnected()
    {
        return isset($this->connection);
    }

    public function doQuery()
    {
        $this->connect();

        mysqli_query($this->connection, $this->query, MYSQLI_ASYNC);
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getAsyncResult()
    {
        return mysqli_reap_async_query($this->connection);
    }
}
