<?php
namespace Bravo3\Tests\Cache\Resources;

use Bravo3\Cache\CachingServiceInterface;
use Bravo3\Cache\CachingServiceTrait;

/**
 * Sample caching service
 */
class CachingService implements CachingServiceInterface
{
    use CachingServiceTrait;
}
