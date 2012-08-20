<?php

namespace AsyncMysql;

use AsyncMysql\EventLoop;
use AsyncMysql\Query;

class Client
{
    private $loop;

    private $host;
    private $user;
    private $password;
    private $dbname;
    private $port;
    private $socket;

    public function __construct(EventLoop $loop, $host = null, $user = null, $password = null, $dbname = null, $port = null, $socket = null)
    {
        $this->loop     = $loop;
        $this->host     = $host;
        $this->user     = $user;
        $this->password = $password;
        $this->dbname   = $dbname;
        $this->port     = $port;
        $this->socket   = $socket;
    }

    public function query($sql)
    {
        return new Query($this->loop, $this, $sql);
    }

    public function getConnection()
    {
        return mysqli_connect($this->host, $this->user, $this->password, $this->dbname, $this->port, $this->socket);
    }
}
