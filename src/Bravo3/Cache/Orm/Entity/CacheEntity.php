<?php
namespace Bravo3\Cache\Orm\Entity;

use Bravo3\Orm\Annotations\Column;
use Bravo3\Orm\Annotations\Entity;
use Bravo3\Orm\Annotations\Id;


/**
 * In order to quickly flush the cache, it is important you have the "key" column sortable without conditions.
 *
 * @Entity(table="cache", sortable_by={"key"})
 */
class CacheEntity implements CacheEntityInterface
{
    /**
     * @var string
     * @Id()
     * @Column(type="string")
     */
    protected $key;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $value;

    /**
     * Get Key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set Key
     *
     * @param string $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * Get Value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set Value
     *
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
}
