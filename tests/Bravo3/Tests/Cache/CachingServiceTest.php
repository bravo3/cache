<?php

namespace Bravo3\Tests\Cache;

use Bravo3\Cache\Ephemeral\EphemeralCachePool;
use Bravo3\Tests\Cache\Resources\CachingService;

class Test extends \PHPUnit_Framework_TestCase
{

    /**
     * @small
     */
    public function testCachingService()
    {
        $service = new CachingService();
        $pool = $service->getCachePool();
        $this->assertTrue($pool instanceof EphemeralCachePool);

        $item = $pool->getItem('test');
        $this->assertFalse($item->isHit());

        $item->set('value', null);
        $this->assertTrue($item->isHit());

        $new_item = $service->getCacheItem('test');
        $this->assertTrue($new_item->isHit());
        $this->assertEquals('value', $new_item->get());
    }

    /**
     * @small
     */
    public function testSetPool()
    {
        $service = new CachingService();
        $pool = new EphemeralCachePool();

        $service->setCachePool($pool);
        $this->assertTrue($service->getCachePool() instanceof EphemeralCachePool);
    }


}
 