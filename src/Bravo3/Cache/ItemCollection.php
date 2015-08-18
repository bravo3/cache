<?php
namespace Bravo3\Cache;

/**
 * Collection of ItemInterface
 */
class ItemCollection implements \IteratorAggregate
{
    protected $items;

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * Retrieve an ArrayIterator containing the ItemInterface list
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * Get an item by key
     *
     * Returns null if the item doesn't exist
     *
     * @param $key
     * @return ItemInterface|null
     */
    public function getItem($key) {
        if (array_key_exists($key, $this->items)) {
            return $this->items[$key];
        } else {
            return null;
        }
    }

    /**
     * Count of items in the collection
     *
     * @return int
     */
    public function count() {
        return count($this->items);
    }

}
 