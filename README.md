Cache Interface
===============
This is a PHP 5.4 caching interface with implementations for common key/value storage engines.

PSR Proposal
------------
The interfaces in this library abide by the current PSR-6 draft for caching interfaces. It should be known that should
PHP-FIG adopt a PSR standard for caching interfaces, the interfaces in this library will be replaced with the PSR
standard.

More info:

* [Crell Proposal - Main](https://github.com/Crell/fig-standards/blob/Cache/proposed/cache.md)
* [Crell Proposal - Cache Meta](https://github.com/Crell/fig-standards/blob/Cache/proposed/cache-meta.md)


Subject To Change
-----------------
The approach taken follows the 'strong item' or 'repository model' as per the above meta documentation. Should the PSR
standard pass, there will be minimal change. Should an alternative approach pass, much of this library - and any
implementations based on the interfaces - will need to be refactored.

A new major version number will be applied to this library when PSR-6 passes.

Usage
=====
Basic usage:

    $pool = new RedisCachePool('tcp://10.0.0.1:6379');
    $item = $pool->get('foo');
    
    $item->get();       // Pull the value from the database
    $item->isHit();     // Check if the retrieval was a cache hit
    
    $item->exists();    // Check if the entry exists in the datbase (MAY avoid actually retrieving the value)
    
    $item->set('bar');  // Save to cache
    $item->delete();    // Remove from cache
    
    $items = $pool->getItems(['test1', 'test2', 'test3']);  // Get a collection of items
    
Using a TTL can be done with a `\DateTime` object or a integer offset in seconds:

    $item = $pool->getItem('foo');
    
    $dt = new \DateTime();
    $dt->modify('+10 seconds');
    
    $item->set('bar', $dt);   // Set TTL with a \DateTime object
    $item->set('bar', null);  // Clear the TTL, item never expires
    $item->set('bar', 10);    // Set the TTL to 10 seconds


Implementations
===============

Ephemeral
---------
The ephemeral implementation is a single-session storage that loses it's data when the session ends. This is useful
for testing or a default implementation in lack of a real cache storage device. Or if you just really don't care about
the volatility of your data.

Redis
-----
Redis support is fully integrated via the Predis library. 

To enable Redis support:

    composer require predis/predis

Bravo3/ORM
----------
You can use any Bravo3/ORM driver to connect a cache connection, this is useful to maintain a single source to your
database.

To enable ORM support:

    composer require bravo3/orm
