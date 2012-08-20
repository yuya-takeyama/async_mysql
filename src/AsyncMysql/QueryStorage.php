<?php

namespace AsyncMysql;

use AsyncMysql\Query;

class QueryStorage implements \Countable
{
    private $queries;

    public function __construct()
    {
        $this->queries = array();
    }

    public function set(Query $query)
    {
        $id = spl_object_hash($query->getConnection());

        $this->queries[$id] = $query;
    }

    public function getQueryByConnection(\mysqli $connection)
    {
        $id = spl_object_hash($connection);

        return $this->queries[$id];
    }

    public function removeQueryByConnection(\mysqli $connection)
    {
        $id = spl_object_hash($connection);

        unset($this->queries[$id]);
    }

    public function getConnections()
    {
        return array_map(function (Query $query) {
            return $query->getConnection();
        }, $this->queries);
    }

    public function count()
    {
        return count($this->queries);
    }
}
