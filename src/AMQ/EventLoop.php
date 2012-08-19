<?php

namespace AMQ;

use AMQ\Client;
use AMQ\Query;
use AMQ\QueryPoller;
use AMQ\QueryStorage;

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
        while ($this->poller->isWaiting()) {
            if ($this->poller->poll()) {
                foreach ($this->poller->getFinishedQueries() as $query) {
                    if ($result = $query->getAsyncResult()) {
                        $query->emit('result', array($result, $query));
                    } else {
                        $query->emit('error', array($query));
                    }
                }
            }
        }
    }
}
