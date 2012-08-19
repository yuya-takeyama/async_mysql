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
            }, array_values($this->relations));
            $error = $reject = array();

            if (mysqli_poll($links, $error, $reject, 1)) {
                foreach ($links as $link) {
                    if ($res = mysqli_reap_async_query($link)) {
                        $query = $this->relations[spl_object_hash($link)];
                        $query->emit('data', array($res, $link, $query));

                        $this->removeQuery($query);
                    }
                }
            }
        }
    }

    public function initConnection()
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

    public function isAnyQueryAvailable()
    {
        return count($this->queries) > 0;
    }
}
