<?php
namespace Bravo3\Cache\Redis;

use Bravo3\Cache\InvalidArgumentException;
use Bravo3\Cache\ItemCollection;
use Bravo3\Cache\ItemInterface;
use Bravo3\Cache\PoolInterface;
use Predis\Client;

/**
 * A PoolInterface wrapper for the Predis library
 *
 * @see https://github.com/nrk/predis
 */
class RedisCachePool implements PoolInterface
{
    /**
     * @var Client
     */
    protected $client = null;

    /**
     * @var mixed
     */
    protected $params;

    /**
     * @var mixed
     */
    protected $options;

    /**
     * Create a new cache pool
     *
     * @param string|array|Client $params Predis client or params, defaults to 'tcp://10.0.0.1:6379'
     * @param mixed               $options
     */
    function __construct($params = null, $options = null)
    {
        if ($params instanceof Client) {
            $this->client  = $params;
            $this->params  = null;
            $this->options = null;
        } else {
            $this->params  = $params;
            $this->options = $options;
        }
    }

    protected function init()
    {
        if ($this->client === null) {
            $this->client = new Client($this->params, $this->options);
        }
    }

    /**
     * Set the Predis client
     *
     * This class lazy-loads the Predis service allowing you to specify your own Predis\Client object directly
     * after constructing a RedisCachePool object
     *
     * @param Client $client
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * Get the Predis client
     *
     * @return Client
     */
    public function getClient()
    {
        $this->init();
        return $this->client;
    }

    /**
     * Returns a Cache Item representing the specified key.
     *
     * This method must always return an ItemInterface object, even in case of
     * a cache miss. It MUST NOT return null.
     *
     * @param string $key The key for which to return the corresponding Cache Item.
     * @return ItemInterface  The corresponding Cache Item.
     * @throws InvalidArgumentException
     */
    public function getItem($key)
    {
        $this->init();
        return new RedisCacheItem($this, $key);
    }

    /**
     * Returns a traversable set of cache items.
     *
     * @param array $keys An indexed array of keys of items to retrieve.
     * @return ItemCollection
     */
    public function getItems(array $keys)
    {
        $this->init();
        $items = array();

        foreach ($keys as $key) {
            $item = new RedisCacheItem($this, $key);
            if ($item->isHit()) {
                $items[$key] = $item;
            }
        }

        return new ItemCollection($items);
    }

    /**
     * Deletes all items in the pool.
     *
     * @return PoolInterface
     */
    public function clear()
    {
        $this->init();
        $this->client->__call('flushdb', []);
        return $this;
    }
}
