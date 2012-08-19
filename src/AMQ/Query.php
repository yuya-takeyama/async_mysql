<?php

namespace AMQ;

use AMQ\EventLoop;
use AMQ\Client;

use Evenement\EventEmitter;

class Query extends EventEmitter
{
    private $loop;

    private $client;

    private $realConnection;

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

    public function getRealConnection()
    {
        $this->realConnect();

        return $this->realConnection;
    }

    public function getConnectionId()
    {
        $this->realConnect();

        return spl_object_hash($this->realConnection);
    }

    public function realConnect()
    {
        if (is_null($this->realConnection)) {
            $this->realConnection = $this->client->getRealConnection();
        }
    }

    public function isConnected()
    {
        return isset($this->realConnection);
    }

    public function doQuery()
    {
        $this->realConnect();

        mysqli_query($this->realConnection, $this->query, MYSQLI_ASYNC);
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getAsyncResult()
    {
        return mysqli_reap_async_query($this->realConnection);
    }
}
