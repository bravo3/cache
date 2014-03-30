<?php
namespace Bravo3\Tests\Cache\Redis;

use Bravo3\Cache\Redis\RedisCacheItem;
use Bravo3\Cache\Redis\RedisCachePool;
use Predis\Client;

/**
 * @group redis
 */
class RedisCacheTest extends \PHPUnit_Framework_TestCase
{


    /**
     * @small
     */
    public function testEphemeralCacheItem()
    {
        $pool = new RedisCachePool();
        $pool->setClient(new Client());

        $pool->clear();
        /** @var $item RedisCacheItem */
        $item = $pool->getItem('miss');
        $this->assertTrue($item instanceof RedisCacheItem);
        $this->assertFalse($item->exists());
        $this->assertEquals('miss', $item->getKey());
        $this->assertFalse($item->isHit());
        $item->set('value');
        $this->assertTrue($item->isHit());
        $this->assertEquals('value', $item->get('value'));
        $item->delete();
        $this->assertFalse($item->isHit());
    }


    /**
     * @small
     */
    public function testEphemeralCacheGetItems()
    {
        $pool = new RedisCachePool(new Client());
        $pool->clear();

        // Prep some data
        $pool->getItem('test1')->set(1);
        $pool->getItem('test2')->set(2);
        $pool->getItem('test3')->set(3);

        // Check we have 3 cache hits
        $items = $pool->getItems(['test1', 'test2', 'test3']);
        $this->assertEquals(3, $items->count());

        $index = 0;
        /** @var $item RedisCacheItem */
        foreach ($items as $key => $item) {
            $index++;
            $this->assertTrue($item instanceof RedisCacheItem);
            $this->assertTrue($item->isHit());
            $this->assertEquals('test'.$index, $key);

            // NB: The value will become a string here!
            $this->assertSame((string)$index, $item->get());
        }

        $this->assertEquals(3, $index);

        $item = $items->getItem('test2');
        $this->assertTrue($item instanceof RedisCacheItem);
        $this->assertEquals(2, $item->get());

        $pool->clear();
        $items = $pool->getItems(['test1', 'test2', 'test3']);
        $this->assertEquals(0, $items->count());

        $item = $items->getItem('test2');
        $this->assertNull($item);
    }

    /**
     * Test valid TTL values set
     *
     * @small
     */
    public function testValidTtl()
    {
        $pool = new RedisCachePool();
        $item = $pool->getItem('test');

        $dt = new \DateTime();
        $dt->modify('+10 seconds');

        $item->set('value', $dt);
        $item->set('value', null);
        $item->set('value', 10);

        $this->assertEquals('value', $item->get());
    }

    /**
     * Test invalid TTL values throw exceptions
     *
     * @small
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidTtl()
    {
        $pool = new RedisCachePool();
        $item = $pool->getItem('test');

        $item->set('value', "hiya!");
    }


}
 