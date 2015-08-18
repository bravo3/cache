<?php
namespace Bravo3\Cache\Orm;

use Bravo3\Cache\ItemInterface;
use Bravo3\Cache\Orm\Entity\CacheEntityInterface;
use Bravo3\Orm\Exceptions\NotFoundException;


/**
 * Bravo3/ORM cache storage
 */
class OrmCacheItem implements ItemInterface
{
    /**
     * @var OrmCachePool
     */
    protected $pool;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
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

    /**
     * @param OrmCachePool $pool
     * @param string       $key
     */
    public function __construct(OrmCachePool $pool, $key)
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

        try {
            /** @var CacheEntityInterface $entity */
            $entity = $this->pool->getEntityManager()->retrieve($this->pool->getEntityClass(), $this->key, false);

            $this->hit   = true;
            $this->value = $entity->getValue();
        } catch (NotFoundException $e) {
            $this->hit   = false;
            $this->value = null;
        }
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
            $exp = (int)$ttl->format('U') - time();
        } elseif (is_int($ttl)) {
            $exp = $ttl;
        } elseif (is_null($ttl)) {
            $exp = null;
        } else {
            throw new \InvalidArgumentException("TTL must be a DateTime object or an integer");
        }

        $entity = $this->createEntity($value);
        $this->pool->getEntityManager()->persist($entity, $exp)->flush();
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

        $entity = $this->createEntity();
        $this->pool->getEntityManager()->delete($entity)->flush();
    }

    /**
     * Tests if the value exists in the cache
     *
     * This has no value over #get() or #isHit() as the data needs to be retrieved from the ORM to test its existance.
     *
     * @return bool True if item exists in the cache, false otherwise.
     */
    public function exists()
    {
        $this->init();
        return $this->isHit();
    }

    /**
     * Creates an entity object
     *
     * @param string $value
     * @return CacheEntityInterface
     */
    protected function createEntity($value = null)
    {
        $class = $this->pool->getEntityClass();

        /** @var CacheEntityInterface $entity */
        $entity = new $class();
        $entity->setKey($this->key);
        $entity->setValue($value);

        return $entity;
    }
}
