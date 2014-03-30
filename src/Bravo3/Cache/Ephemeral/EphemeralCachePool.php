<?php
namespace Bravo3\Cache\Ephemeral;

use Bravo3\Cache\ItemCollection;
use Bravo3\Cache\PoolInterface;

/**
 * A single-session non-persistent cache pool
 */
class EphemeralCachePool implements PoolInterface
{
    protected $pool = [];

    /**
     * Returns a Cache Item representing the specified key.
     *
     * This method must always return an ItemInterface object, even in case of
     * a cache miss. It MUST NOT return null.
     *
     * @param string $key
     *   The key for which to return the corresponding Cache Item.
     * @return \Bravo3\Cache\ItemInterface
     *   The corresponding Cache Item.
     * @throws \Bravo3\Cache\InvalidArgumentException
     *   If the $key string is not a legal value an InvalidArgumentException
     *   MUST be thrown.
     */
    public function getItem($key)
    {
        if (array_key_exists($key, $this->pool)) {
            return $this->pool[$key];
        } else {
            $item             = new EphemeralCacheItem($key);
            $this->pool[$key] = $item;
            return $item;
        }
    }

    /**
     * Returns a traversable set of cache items.
     *
     * @param array $keys
     *   An indexed array of keys of items to retrieve.
     * @return ItemCollection
     *   A traversable collection of Cache Items in the same order as the $keys
     *   parameter, keyed by the cache keys of each item. If no items are found
     *   an empty Traversable collection will be returned.
     */
    public function getItems(array $keys)
    {
        $item_list = [];
        foreach ($keys as $key) {
            $item = $this->getItem($key);
            if ($item->isHit()) {
                $item_list[$key] = $item;
            }
        }
        return new ItemCollection($item_list);
    }

    /**
     * Deletes all items in the pool.
     *
     * @return PoolInterface
     *   The current pool.
     */
    public function clear()
    {
        $this->pool = [];
    }

}
 