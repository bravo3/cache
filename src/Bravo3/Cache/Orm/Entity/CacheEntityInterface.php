<?php
namespace Bravo3\Cache\Orm\Entity;


interface CacheEntityInterface
{
    /**
     * Get Key
     *
     * @return string
     */
    public function getKey();

    /**
     * Set Key
     *
     * @param string $key
     * @return $this
     */
    public function setKey($key);

    /**
     * Get Value
     *
     * @return string
     */
    public function getValue();

    /**
     * Set Value
     *
     * @param string $value
     * @return $this
     */
    public function setValue($value);
}
