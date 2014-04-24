<?php
namespace Bravo3\Tests\Cache\Emphemeral;

use Bravo3\Cache\Ephemeral\EphemeralCacheItem;
use Bravo3\Cache\Ephemeral\EphemeralCachePool;

/**
 * @group ephemeral
 */
class EphemeralCacheTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @small
     */
    public function testEphemeralCacheItem()
    {
        $pool = new EphemeralCachePool();
        $item = $pool->getItem('miss');
        $this->assertTrue($item instanceof EphemeralCacheItem);
        $this->assertEquals('miss', $item->getKey());
        $this->assertFalse($item->isHit());
        $this->assertFalse($item->exists());
        $item->set('value');
        $this->assertTrue($item->isHit());
        $item->delete();
        $this->assertFalse($item->isHit());
    }

    /**
     * @small
     */
    public function testEphemeralCacheGetItems()
    {
        $pool = new EphemeralCachePool();
        $pool->clear();

        // Prep some data
        $pool->getItem('test1')->set(1);
        $pool->getItem('test2')->set(2);
        $pool->getItem('test3')->set(3);

        // Check we have 3 cache hits
        $items = $pool->getItems(['test1', 'test2', 'test3']);
        $this->assertEquals(3, $items->count());

        $index = 0;
        /** @var $item EphemeralCacheItem */
        foreach ($items as $key => $item) {
            $index++;
            $this->assertTrue($item instanceof EphemeralCacheItem);
            $this->assertTrue($item->isHit());
            $this->assertEquals('test'.$index, $key);
            $this->assertSame($index, $item->get());
        }

        $this->assertEquals(3, $index);

        $item = $items->getItem('test2');
        $this->assertTrue($item instanceof EphemeralCacheItem);
        $this->assertEquals(2, $item->get());

        $pool->clear();
        $items = $pool->getItems(['test1', 'test2', 'test3']);
        $this->assertEquals(0, $items->count());

        $item = $items->getItem('test2');
        $this->assertNull($item);
    }

    /**
     * @small
     */
    public function testPoolReferences()
    {
        $pool = new EphemeralCachePool();
        $pool->clear();

        // Prep some data
        $item_a = $pool->getItem('test');
        $item_a->set(1, null);

        $item_b = $pool->getItem('test');
        $this->assertTrue($item_b->exists());
        $this->assertEquals(1, $item_b->get());
    }

    /**
     * Test valid TTL values set
     *
     * @small
     */
    public function testValidTtl()
    {
        $pool = new EphemeralCachePool();
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
        $pool = new EphemeralCachePool();
        $item = $pool->getItem('test');

        $item->set('value', "hiya!");
    }


}
