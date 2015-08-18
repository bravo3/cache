<?php
namespace Bravo3\Cache\Orm;

use Bravo3\Cache\ItemCollection;
use Bravo3\Cache\Orm\Entity\CacheEntity;
use Bravo3\Cache\PoolInterface;
use Bravo3\Orm\Exceptions\NotFoundException;
use Bravo3\Orm\Query\SortedTableQuery;
use Bravo3\Orm\Services\EntityManager;


/**
 * Bravo3/ORM cache pool
 */
class OrmCachePool implements PoolInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $entity_class;

    /**
     * @param EntityManager $em           ORM EntityManager
     * @param string        $entity_class Class name of entity to use, defaults to `CacheEntity::class`
     */
    public function __construct(EntityManager $em, $entity_class = null)
    {
        $this->em           = $em;
        $this->entity_class = $entity_class ?: CacheEntity::class;
    }

    /**
     * Get the underlying entity manager
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }

    /**
     * Set the underlying entity manager
     *
     * @param EntityManager $em
     * @return $this
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
        return $this;
    }

    /**
     * Get the class name of the cache entity
     *
     * @return string
     */
    public function getEntityClass()
    {
        return $this->entity_class;
    }

    /**
     * Set the class name of the cache entity
     *
     * @param string $entity_class
     * @return $this
     */
    public function setEntityClass($entity_class)
    {
        $this->entity_class = $entity_class;
        return $this;
    }

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
        return new OrmCacheItem($this, $key);
    }

    /**
     * Returns a traversable set of cache items.
     *
     * @param array $keys An indexed array of keys of items to retrieve.
     * @return ItemCollection
     */
    public function getItems(array $keys)
    {
        $items = array();

        foreach ($keys as $key) {
            $item = new OrmCacheItem($this, $key);
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
        $items = $this->em->sortedQuery(new SortedTableQuery($this->entity_class, 'key'), false, false);
        $index = 0;

        $items->rewind();
        while ($items->valid()) {
            try {
                $item = $items->current();
                $this->em->delete($item);

                if (++$index % 100 == 0) {
                    $this->em->flush();
                }
            } catch (NotFoundException $e) {
                // Assume item expired, skip
            }

            $items->next();
        }

        $this->em->flush();

        return $this;
    }
}
