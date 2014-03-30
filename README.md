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

Implementations
===============

Ephemeral
----------
The ephemeral implementation is a single-session storage that loses it's data when the session ends. This is useful
for testing or a default implementation in lack of a real cache storage device. Or if you just really don't care about
the volatility of your data.

Doctrine
--------
Consider: native PDO as well?

DynamoDB
--------
Consider: SimpleDB?

Memcached
---------

Redist
------
