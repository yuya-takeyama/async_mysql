<?php

namespace AMQ;

use AMQ\Query;
use AMQ\QueryStorage;

class QueryPoller
{
    private $sec;

    private $usec;

    private $finishedQueries;

    public function __construct($sec = 0, $usec = 0)
    {
        $this->sec  = $sec;
        $this->usec = $usec;
        $this->queryStorage = new QueryStorage;
    }

    public function addQuery(Query $query)
    {
        if (!$query->isExecuted()) {
            $query->execute();
        }

        $this->queryStorage->set($query);
    }

    public function poll()
    {
        $connections = $this->queryStorage->getConnections();
        $error = $reject = array();

        $ret = mysqli_poll($connections, $error, $reject, $this->sec, $this->usec);

        if ($ret) {
            $this->setFinishedConnections($connections);
            $this->removeQueriesByConnections($connections);
        }

        return $ret;
    }

    private function setFinishedConnections($connections)
    {
        $storage = $this->queryStorage;

        $this->finishedQueries = array_map(function (\mysqli $connection) use ($storage) {
            return $storage->getQueryByConnection($connection);
        }, $connections);
    }

    public function getFinishedQueries()
    {
        return $this->finishedQueries;
    }

    public function isWaiting()
    {
        return count($this->queryStorage) > 0;
    }

    private function removeQueriesByConnections($connections)
    {
        foreach ($connections as $connection) {
            $query = $this->queryStorage->removeQueryByConnection($connection);
        }
    }
}
