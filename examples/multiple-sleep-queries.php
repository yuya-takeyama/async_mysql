<?php
require __DIR__ . '/../vendor/autoload.php';

$loop = new AsyncMysql\EventLoop;

$conn = $loop->connect('localhost');

foreach (array(3, 6, 9, 1, 5, 2) as $i => $sleep) {
    $query = $conn->query("SELECT {$i} AS `id`, SLEEP({$sleep}) AS `sleep_{$sleep}`");

    $query->on('result', function ($result, $query) use ($i) {
        echo "{$i}: ", $query->getQuery(), PHP_EOL;
        $records = $result->fetch_all(MYSQLI_ASSOC);
        var_dump($records);
        echo PHP_EOL;
    });

    $query->on('error', function ($query) {
        echo "Error: ", $query->getQuery(), PHP_EOL;
        echo PHP_EOL;
    });
}

$loop->run();
