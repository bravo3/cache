Ephemeral Driver
================
The ephemeral driver is an in-memory cache driver. It is useful for "soft caching" or for testing in the absence of a
solid driver. 

Installation
------------
The ephemeral driver has no additional requirements.
    
Usage
-----
There are no options required for this pool:

    $pool = new EphemeralCachePool();
