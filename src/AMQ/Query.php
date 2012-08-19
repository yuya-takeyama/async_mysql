<?php

namespace AMQ;

use AMQ\EventLoop;
use AMQ\Connection;

use Evenement\EventEmitter;

class Query extends EventEmitter
{
    private $loop;

    private $connection;

    private $realConnection;

    private $query;

    private $id;

    public function __construct(EventLoop $loop, Connection $connection, $query)
    {
        $this->loop       = $loop;
        $this->connection = $connection;
        $this->query      = $query;

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
            $this->realConnection = $this->connection->getRealConnection();
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
}
