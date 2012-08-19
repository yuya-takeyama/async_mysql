<?php

namespace AMQ;

use AMQ\Connection;
use AMQ\Query;

class EventLoop
{
    private $relations;

    public function __construct()
    {
        $this->queries   = new \SplObjectStorage;
        $this->relations = array();
    }

    public function connect($host = null, $user = null, $password = null, $dbname = null, $port = null, $socket = null)
    {
        return new Connection($this, $host, $user, $password, $dbname, $port, $socket);
    }

    public function addQuery(Query $query)
    {
        $this->queries->attach($query);
    }

    public function removeQuery(Query $query)
    {
        $this->queries->detach($query);
    }

    public function run()
    {
        $this->initConnection();

        while ($this->isAnyQueryAvailable()) {
            $links = array_map(function ($query) {
                return $query->getRealConnection();
            }, $this->relations);
            $error = $reject = array();

            if (mysqli_poll($links, $error, $reject, 1)) {
                foreach ($links as $link) {
                    $id    = spl_object_hash($link);
                    $query = $this->relations[$id];

                    unset($this->relations[$id]);
                    $this->removeQuery($query);

                    if ($data = mysqli_reap_async_query($link)) {
                        $query->emit('data', array($data, $query));
                    } else {
                        $query->emit('error', array($query));
                    }
                }
            }
        }
    }

    private function initConnection()
    {
        foreach ($this->queries as $query) {
            if (!$query->isConnected()) {
                $conn = $query->getRealConnection();
                $id   = spl_object_hash($conn);

                $query->doQuery();

                $this->relations[$id] = $query;
            }
        }
    }

    private function isAnyQueryAvailable()
    {
        return count($this->queries) > 0;
    }
}
