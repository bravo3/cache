<?php
namespace Bravo3\Cache\Redis;

use Bravo3\Cache\ItemInterface;

/**
 * Cache items belonging to a Redis pool
 */
class RedisCacheItem implements ItemInterface
{
    /**
     * @var RedisCachePool
     */
    protected $pool;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var mixed
     */
    protected $value = null;

    /**
     * @var bool
     */
    protected $hit = false;

    /**
     * @var bool
     */
    protected $loaded = false;

    function __construct(RedisCachePool $pool, $key)
    {
        $this->pool = $pool;
        $this->key  = $key;
    }

    /**
     * Makes an exists and get call to the redis server
     */
    protected function init()
    {
        if ($this->loaded) {
            return;
        }

        $responses = $this->execMulti(
            [
                ['cmd' => 'exists', 'params' => [$this->key]],
                ['cmd' => 'get', 'params' => [$this->key]]
            ]
        );

        $this->hit   = $responses[0];
        $this->value = $responses[1];
    }

    /**
     * Returns the key for the current cache item.
     *
     * The key is loaded by the Implementing Library, but should be available to
     * the higher level callers when needed.
     *
     * @return string
     *   The key string for this cache item.
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Retrieves the value of the item from the cache associated with this objects key.
     *
     * The value returned must be identical to the value original stored by set().
     *
     * if isHit() returns false, this method MUST return null. Note that null
     * is a legitimate cached value, so the isHit() method SHOULD be used to
     * differentiate between "null value was found" and "no value was found."
     *
     * @return mixed
     *   The value corresponding to this cache item's key, or null if not found.
     */
    public function get()
    {
        $this->init();
        return $this->value;
    }

    /**
     * Stores a value into the cache.
     *
     * The $value argument may be any item that can be serialized by PHP,
     * although the method of serialization is left up to the Implementing
     * Library.
     *
     * Implementing Libraries MAY provide a default TTL if one is not specified.
     * If no TTL is specified and no default TTL has been set, the TTL MUST
     * be set to the maximum possible duration of the underlying storage
     * mechanism, or permanent if possible.
     *
     * @param mixed         $value
     *     The serializable value to be stored.
     * @param int|\DateTime $ttl
     *     - If an integer is passed, it is interpreted as the number of seconds
     *     after which the item MUST be considered expired.
     *     - If a DateTime object is passed, it is interpreted as the point in
     *     time after which the the item MUST be considered expired.
     *     - If no value is passed, a default value MAY be used. If none is set,
     *     the value should be stored permanently or for as long as the
     *     implementation allows.
     * @return bool
     *     Returns true if the item was successfully saved, or false if there was
     *     an error.
     */
    public function set($value = null, $ttl = null)
    {
        $this->value  = $value;
        $this->hit    = true;
        $this->loaded = true;

        if ($ttl instanceof \DateTime) {
            $exp = $ttl;
        } elseif (is_int($ttl)) {
            $exp = new \DateTime(date('c', time() + $ttl));
        } elseif (is_null($ttl)) {
            $exp = null;
        } else {
            throw new \InvalidArgumentException("TTL must be a DateTime object or an integer");
        }

        $cmds = [['cmd' => 'set', 'params' => [$this->key, $this->value]]];

        if ($exp) {
            $cmds[] = ['cmd' => 'expireat', 'params' => [$this->key, $exp->getTimestamp()]];
        } else {
            $cmds[] = ['cmd' => 'persist', 'params' => [$this->key]];
        }

        $this->execMulti($cmds);

        return true;
    }

    /**
     * Confirms if the cache item lookup resulted in a cache hit.
     *
     * Note: This method MUST NOT have a race condition between calling isHit()
     * and calling get().
     *
     * @return bool
     *   True if the request resulted in a cache hit.  False otherwise.
     */
    public function isHit()
    {
        $this->init();
        return $this->hit;
    }

    /**
     * Removes the current key from the cache.
     *
     * @return ItemInterface
     *   The current item.
     */
    public function delete()
    {
        $this->value  = null;
        $this->hit    = false;
        $this->loaded = false;
        $this->exec('del', [$this->key]);
    }

    /**
     * Confirms if the cache item exists in the cache.
     *
     * Note: This method MAY avoid retrieving the cached value for performance
     * reasons, which could result in a race condition between exists() and get().
     *
     * @return bool
     *  True if item exists in the cache, false otherwise.
     */
    public function exists()
    {
        return $this->exec('exists', [$this->key]);
    }

    /**
     * Execute a single redis command
     *
     * @param string $cmd
     * @param mixed  $params
     * @return mixed
     */
    protected function exec($cmd, $params)
    {
        return $this->pool->getClient()->__call($cmd, $params);
    }

    /**
     * Execute a wad of redis commands
     *
     * @param array $cmds array of ['cmd' => .., 'params' => []]
     * @return array
     */
    protected function execMulti(array $cmds)
    {
        $pipeline = $this->pool->getClient()->pipeline();

        foreach ($cmds as $cmd) {
            $pipeline->__call($cmd['cmd'], $cmd['params']);
        }

        return $pipeline->execute();
    }

}
 