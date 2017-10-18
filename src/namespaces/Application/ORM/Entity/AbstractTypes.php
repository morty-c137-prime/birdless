<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

namespace Application\ORM\Entity;

/**
 * This class represents a static representation of all possible types an entity
 * can singularly take.
 */
abstract class AbstractTypes
{
    /**
     * Type data array with key-value elements of the form:
     * 
     * $type_id => (object) [
     *     name => ...
     * ]
     *
     * XXX: This property should be overridden in any extending subclass.
     * 
     * @var array
     */
    protected static $data = [];

    /**
     * Returns all types in this context in array form with type names as the
     * keys and respective IDs as the values.
     * @return array
     */
    public static function toArray()
    {
        $dataArray = [];

        foreach(static::$data as $id => $type_data)
            $dataArray[$type_data->name] = $id;

        return $dataArray;
    }

    /**
     * This function should be called initialize the internal $data property.
     */
    abstract public static function init();

    public static function fromTypeData($formattedArrayDataString, $classConsts)
    {
        $thisClass = static::class;
        $class = <<<CLS
return new class extends $thisClass
{
    public static function init()
    {
        static::\$data = [$formattedArrayDataString
        ];
    }
    $classConsts
};
CLS;
        $instance = eval($class);
        $instance::init();
        return $instance;
    }
}
