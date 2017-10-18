<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

namespace Application\ORM\Entity;

use Application\ORM\Database\WritablePDORow;

/**
 * The generic base class for an EntityInterface implementation.
 *
 * Note that it is advisable to override the __get() magic method to facilitate
 * sub-entities (see README). If your entity does not itself contain other
 * entities, then you can safely ignore this.
 */
abstract class AbstractEntity implements EntityInterface
{
    /**
     * Internal data backing store for this entity.
     * 
     * @var mixed
     */
    protected $data = NULL;

    /**
     * Result of calling static::getSchema() stored as camelCase without
     * underscores. Use static::getSchema() to get the original DB schema.
     * 
     * @var array
     */
    protected $schema = [];

    /**
     * Mapping of new camelCase schema elements to the old uncivilized schema
     * elements.
     * 
     * @var array
     */
    protected $schemaMap = [];

    /**
     * The internal record of all properties that have been changed.
     * 
     * @var array
     */
    protected $mutatedProperties = [];

    /**
     * Entity constructor. Accepts an array of data that will be initially set
     * without causing any properties to be flagged as mutated. If you're
     * creating a new instance with the goal of later inserting it as a new row
     * via database gateway, you should use static::setData() instead of passing
     * $data into the constructor.
     * 
     * @param mixed $data Data corresponding to some internal schema.
     */
    public function __construct($data = [])
    {
        $this->schema = array_map(
            (function($prop)
            {
                $newprop = lcfirst(str_replace('_', '', ucwords($prop, '_')));
                $this->schemaMap[$newprop] = $prop;
                return $newprop;
            })->bindTo($this),
            $this->getSchema()
        );

        $this->setDataWithoutMutation($data);
    }

    public function __get($property)
    {
        if(!in_array($property, $this->schema))
            throw new \InvalidArgumentException("$property is not a valid entity property for " . $this->toString());
        
        return $this->getDataProperty($property);
    }

    public function __set($property, $value)
    {
        if(!in_array($property, $this->schema))
            throw new \InvalidArgumentException("$property is not a valid property for " . $this->toString());

        $this->data->{$this->schemaMap[$property]} = $value;
        $this->mutatedProperties[$property] = TRUE;
    }

    public function __isset($property)
    {
        return isset($this->schemaMap[$property]) && isset($this->data->{$this->schemaMap[$property]});
    }

    /**
     * Return the record of properties whose values have been mutated since the
     * record was last cleared. See static::clearMutatedPropertyArray().
     * 
     * @return array One-dimensional list of entity property names
     */
    public function getMutatedPropertyArray()
    {
        return array_keys($this->mutatedProperties);
    }

    /**
     * Clear the record of properties whose values have been mutated.
     */
    public function clearMutatedPropertyArray()
    {
        $this->mutatedProperties = [];
    }

    /**
     * Set the internal data store manually. $data must be an object or an
     * array. This method is not intended to be called directly in most cases.
     * Instead, you should just mutate the instance properties instead.
     * 
     * @param mixed $data An object or array
     */
    public function setData($data)
    {
        $this->clearMutatedPropertyArray();
        $this->setDataWithoutMutation($data);
        $this->markAllPropertiesAsMutated();
    }

    /**
     * Determine if this entity is equal to some other entity. Returns true if
     * both entities have the same unique id and are of the exact same type.
     * 
     * @param  EntityInterface $entity
     * @return bool TRUE if the entities are the same, else FALSE
     */
    public function equals(EntityInterface $entity)
    {
        return $this->getUniqueId() === $entity->getUniqueId() && get_class($entity) == static::class;
    }

    /**
     * Returns this entity's data represented as an array of key-value pairs.
     * 
     * @return array
     */
    public function toArray()
    {
        return (array) $this->data;
    }

    /**
     * Returns a string representation of this entity.
     * 
     * @return string
     */
    public function toString()
    {
        return '<(' . str_replace(__NAMESPACE__ . '\\', '', static::class) . ') uniqueid=' . $this->getUniqueId() . '>';
    }

    /**
     * Returns a string representation of this entity. Magic alias of
     * static::toString().
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Get a property from the internal data object without going through the
     * get magic method. Use this method to avoid the entity-ification of data
     * properties. Note that the value returned may still be an entity object.
     *
     * @param string $property
     */
    public function getDataProperty($property)
    {
        $property = $this->schemaMap[$property] ?? $property;

        if(!property_exists($this->data, $property) && !isset($this->data->{$property}))
            return NULL;

        return $this->data->{$property};
    }

    /**
     * The same as static::setData() except it does not touch the mutation
     * record.
     * 
     * @param mixed $data
     */
    protected function setDataWithoutMutation($data)
    {
        if($data instanceof \PDORow)
            $data = new WritablePDORow($data);

        else if(!is_object($data))
            $data = (object) $data;

        $this->data = $data;
    }

    /**
     * Mark all properties as mutated in the internal mutated properties array.
     * Note that this method was not meant to be called directly.
     */
    protected function markAllPropertiesAsMutated()
    {
        array_map(
            function($prop)
            {
                if(array_key_exists($prop, $this->schemaMap))
                    $this->mutatedProperties[$this->schemaMap[$prop]] = TRUE;
            },
            array_keys(get_object_vars($this->data))
        );
    }

    /**
     * Returns some unique (per table) identifier representing this entity.
     * 
     * @return mixed
     */
    abstract public function getUniqueId();

    /**
     * Commits all mutated properties of this entity. It is recommended that
     * this method reset the mutation record via
     * static::clearMutatedPropertyArray().
     * 
     * @return bool TRUE if the entity was commited, FALSE if it was not for
     * whatever reason
     */
    abstract public function commit();

    /**
     * Returns the internal data schema (flat array of strings) this entity
     * adheres to.
     * 
     * @return array Key-value array representing this entity's schema.
     */
    abstract public static function getSchema();
}
