<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

namespace Application\ORM\Entity;

/**
 * This class represents a static representation of all possible relationship
 * types between entities within the ORM. These types come with descriptions.
 */
abstract class AbstractDescribedTypes extends AbstractTypes
{
    /**
     * Relation type data array with key-value elements of the form:
     * 
     * $relation_type_id => (object) [
     *     name => ...,
     *     description => ...
     * ]
     *
     * This property should be overridden in any extending subclass.
     * 
     * @var array
     */
    protected static $data = [];

    /**
     * Returns the description for a specific type ID or an empty string if
     * there is not a description available.
     * 
     * @param $typeId
     * @return string
     */
    public static function getDescriptionFor($typeId)
    {
        if(!array_key_exists($typeId, static::$data))
            throw new \InvalidArgumentException("Type id=$typeId does not exist in this context");

        return static::$data[$typeId]->description ?? '';
    }

    /**
     * This function should be called initialize the internal $data property.
     */
    // abstract public static function init();
}
