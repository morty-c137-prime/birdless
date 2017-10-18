<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

namespace Application\ORM\Database;

use \PDORow;

/**
 * A cousin to (that does not extend) the \PDORow class. It exposes the exact
 * same API save for 
 */
class WritablePDORow /* extends PDORow // cannot be done as PDORow is final */
{
    protected $pdoRow = NULL;
    protected $dataOverlayMap = [];

    public function __construct($pdoRow) // Since PDORow is final...
    {
        $this->pdoRow = (object) $pdoRow;
    }

    public function __get($property)
    {
        return $this->dataOverlayMap[$property] ?? $this->pdoRow->{$property};
    }

    public function __set($property, $value)
    {
        $this->dataOverlayMap[$property] = $value;
    }

    public function __isset($property)
    {
        return property_exists($this->pdoRow, $property) || isset($this->pdoRow->{$property});
    }

    public function __call($name, $args)
    {
        return $this->pdoRow->{$name}(...$args);
    }

    public function __unset($property)
    {
        unset($this->pdoRow->{$property});
    }

    public function __toString()
    {
        return (string) $this->pdoRow;
    }

    public function __clone()
    {
        return clone $this->pdoRow;
    }
}
