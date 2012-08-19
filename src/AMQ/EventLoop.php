<?php

namespace AMQ;

use AMQ\Client;
use AMQ\Query;
use AMQ\QueryStorage;

class EventLoop
{
    private $queries;

    private $queryStorage;

    public function __construct()
    {
        $this->queries = new \SplObjectStorage;
        $this->queryStorage = new QueryStorage;
    }

    public function connect($host = null, $user = null, $password = null, $dbname = null, $port = null, $socket = null)
    {
        return new Client($this, $host, $user, $password, $dbname, $port, $socket);
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
        $this->initAllQueries();

        while ($this->isAnyQueryAvailable()) {
            $links = $this->queryStorage->getConnections();
            $error = $reject = array();

            if (mysqli_poll($links, $error, $reject, 1)) {
                foreach ($links as $link) {
                    $query = $this->queryStorage->getQueryByConnection($link);

                    if ($result = $query->getAsyncResult($link)) {
                        $query->emit('result', array($result, $query));
                    } else {
                        $query->emit('error', array($query));
                    }

                    $this->removeQuery($query);
                    $this->queryStorage->removeQueryByConnection($link);
                }
            }
        }
    }

    private function initAllQueries()
    {
        foreach ($this->queries as $query) {
            if (!$query->isConnected()) {
                $query->execute();
                $this->queryStorage->set($query);
            }
        }
    }

    private function isAnyQueryAvailable()
    {
        return count($this->queries) > 0;
    }
}
