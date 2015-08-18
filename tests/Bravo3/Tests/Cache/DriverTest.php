<?php
namespace Bravo3\Tests\Cache\Redis;

use Bravo3\Cache\Ephemeral\EphemeralCachePool;
use Bravo3\Cache\ItemCollection;
use Bravo3\Cache\ItemInterface;
use Bravo3\Cache\PoolInterface;
use Bravo3\Cache\Redis\RedisCachePool;

/**
 * @group redis
 */
class DriverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider poolProvider
     * @param PoolInterface $pool
     */
    public function testItem(PoolInterface $pool)
    {
        $pool->clear();
        $item = $pool->getItem('miss');
        $this->assertTrue($item instanceof ItemInterface);
        $this->assertFalse($item->exists());
        $this->assertEquals('miss', $item->getKey());
        $this->assertFalse($item->isHit());
        $item->set('value');
        $this->assertTrue($item->isHit());
        $this->assertEquals('value', $item->get());
        $item->delete();
        $this->assertFalse($item->isHit());
    }

    /**
     * @dataProvider poolProvider
     * @param PoolInterface $pool
     */
    public function testGetItems(PoolInterface $pool)
    {
        $pool->clear();

        // Prep some data
        $pool->getItem('test1')->set(1);
        $pool->getItem('test2')->set(2);
        $pool->getItem('test3')->set(3);

        // Check we have 3 cache hits
        /** @var ItemCollection $items */
        $items = $pool->getItems(['test1', 'test2', 'test3']);
        $this->assertCount(3, $items);

        $index = 0;
        /** @var $item ItemInterface */
        foreach ($items as $key => $item) {
            $index++;
            $this->assertTrue($item instanceof ItemInterface);
            $this->assertTrue($item->isHit());
            $this->assertEquals('test'.$index, $key);
            $this->assertEquals($index, $item->get());
        }

        $this->assertEquals(3, $index);

        $item = $items->getItem('test2');
        $this->assertTrue($item instanceof ItemInterface);
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
     * @dataProvider poolProvider
     * @param PoolInterface $pool
     */
    public function testValidTtl(PoolInterface $pool)
    {
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
     * @dataProvider poolProvider
     * @param PoolInterface $pool
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidTtl(PoolInterface $pool)
    {
        $item = $pool->getItem('test');
        $item->set('value', "hiya!");
    }

    /**
     * Provides all pool implementations
     *
     * @return array
     */
    public function poolProvider()
    {
        return [
            [new EphemeralCachePool()],
            [new RedisCachePool()],
        ];
    }
}
