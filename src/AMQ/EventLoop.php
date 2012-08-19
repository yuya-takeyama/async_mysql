<?php

namespace AMQ;

use AMQ\Client;
use AMQ\Query;
use AMQ\QueryPoller;

class EventLoop
{
    private $poller;

    public function __construct()
    {
        $this->poller = new QueryPoller(1);
    }

    public function connect($host = null, $user = null, $password = null, $dbname = null, $port = null, $socket = null)
    {
        return new Client($this, $host, $user, $password, $dbname, $port, $socket);
    }

    public function addQuery(Query $query)
    {
        $this->poller->addQuery($query);
    }

    public function removeQuery(Query $query)
    {
        $this->poller->removeQuery($query);
    }

    public function run()
    {
        while ($this->poller->isUnfinished()) {
            if ($this->poller->poll()) {
                $queries = $this->poller->getFinishedQueries();

                foreach ($queries as $query) {
                    $query->handleAsyncResult();
                }
            }
        }
    }
}
