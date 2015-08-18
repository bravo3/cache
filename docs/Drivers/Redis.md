Redis Driver
============

The Redis driver is a minimalistic Predis client, suitable for basic Redis useage. 

Installation
------------
On the command line, add Predis to your project via Composer:

    composer require predis/predis
    
Usage
-----
By default, the RedisCachePool will connect to a local Redis server:

    $pool = new RedisCachePool();
    
You can pass the same parameters and options you would pass to Predis, or give the cache pool an instance of the
`Predis\Client` class:

    $pool = new RedisCachePool($params, $options);

    $client = new \Predis\Client($params, $options);
    $pool = new RedisCachePool($client);
