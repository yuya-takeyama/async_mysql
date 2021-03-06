AsyncMysql
==========

This is an example project provides APIs to execute MySQL queries asynchronously using [MySQL Native Driver](http://www.php.net/manual/ja/book.mysqlnd.php)

This is just an example project
-------------------------------

I'll not develop this project continuously.  
But I want to integrate something like this into [React](https://github.com/react-php/react)

But MySQL Native Driver can't be polled using `stream_select()`.  
So any advices are welcome.

Example
-------

This example executes multiple sleeping query in parallel.

```php
<?php
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
```

Author
------

Yuya Takeyama
