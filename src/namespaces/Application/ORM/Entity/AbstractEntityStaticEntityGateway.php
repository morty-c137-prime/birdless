<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

namespace Application\ORM\Entity;

use Application\ORM\Database\DatabaseGatewayInterface;
use Application\ORM\Exception\ORMEntityNotFoundException;
use Application\ORM\Exception\ORMGenericException;

/**
 * The generic base class for an EntityInterface implementation that is also its
 * own static gateway. In essence, this class represents both a type of abstract
 * entity and the gateway for all abstract entities of that type. Inspired by
 * the active record pattern and Ruby's Active Record.
 *
 * When extending this class, be sure to redeclare the static $databaseGateway
 * property if you do not want the connection object to be shared.
 */
abstract class AbstractEntityStaticEntityGateway extends AbstractEntity implements StaticEntityGatewayInterface
{
    /**
     * XXX: Redeclare this property when subclassing this abstract class if you
     * do not want the connection object to be shared.
     * 
     * @var \DatabaseGatewayInterface
     */
    protected static $databaseGateway;

    /**
     * Return the static database gateway instance.
     * 
     * @return DatabaseGatewayInterface
     */
    public static function getDatabaseGateway()
    {
        if(!isset(static::$databaseGateway))
            throw new ORMGenericException(
                'Attempted to call ' . static::class . '::getDatabaseGateway() without a set database gateway');

        return static::$databaseGateway;
    }

    /**
     * Set the internal static database gateway instance.
     * 
     * @param DatabaseGatewayInterface $gateway
     */
    public static function setDatabaseGateway(DatabaseGatewayInterface $gateway)
    {
        static::$databaseGateway = $gateway;
    }

    /**
     * Wrapper method around calling query on the internal gateway instance.
     * 
     * @param  string $query  Valid (parameterized) SQL
     * @param  array  $params Parameters to pass, if any
     * @return \PDOStatement  PDO query result
     */
    protected static function query($query, $params = [])
    {
        return static::getDatabaseGateway()->query($query, $params);
    }

    /**
     * Returns a new instance of this class configured as a valid and stateful
     * entity.
     * 
     * @param  mixed $id
     * @return static::class
     */
    public static function fromUniqueId($id)
    {
        $id = (array) $id;
        $primaries = (array) static::getPrimaryProperties();

        if(count($primaries) != count($id))
            throw new \InvalidArgumentException(static::class . '::fromUniqueId($id) called with incompatible id');

        $table = static::getOriginTable();
        $where = static::constructParameterizedWhereClause($primaries);

        if($row = static::query("SELECT * FROM `$table` WHERE $where LIMIT 1", $id)->fetch())
            return new static($row);

        throw new ORMEntityNotFoundException(static::class, $id);
    }

    /**
     * Commits all mutated properties of $entity. This method does not clear the
     * mutated properties array. If that is desired, use User::commit() instead.
     * This method will return FALSE if there are no mutated properties.
     * 
     * @param  EntityInterface $entity
     * @return bool TRUE if the entity was commited, FALSE if it was not
     */
    public static function commitEntity(EntityInterface $entity)
    {
        $update = FALSE;

        if(!($entity instanceof static))
        {
            $class = get_class($entity);
            throw new \InvalidArgumentException(
                "Attempted to commit entity of type $class, expected type " . static::class);
        }

        if(!count($entity->getMutatedPropertyArray()))
            return FALSE;

        $exists = $entity->existsInBackend();
        $query = $exists
            ? $entity->toParameterizedUpdateString()
            : $entity->toParameterizedInsertString();

        $params = [];

        foreach($entity->getMutatedPropertyArray() as $prop)
        {
            $val = $entity->getDataProperty($prop);
            
            if($val instanceof EntityInterface)
                $val = $val->getUniqueId();

            $params[] = $val;
        }
        
        static::query($query, !$exists ? $params : array_merge($params, (array) $entity->getUniqueId()));

        return TRUE;
    }

    /**
     * Interpolate an array of items into a parameterized where clause string.
     * 
     * @param  array  $items
     * @return string
     */
    protected static function constructParameterizedWhereClause(array $items)
    {
        return implode(' AND ', array_map(function($item){ return '`' . $item . '` = ?'; }, $items));
    }

    /**
     * Commits all mutated properties of this entity. This is the instance
     * interface to the static method static::commitEntity() with one crucial
     * difference: this method also clears the mutated properties array while
     * static::commitEntity() does not.
     */
    public function commit()
    {
        $ret = $this->commitEntity($this);
        $this->clearMutatedPropertyArray();
        
        return $ret;
    }

    /**
     * The same as AbstractEntityStaticEntityGateway::commit(), except all
     * properties will be marked as mutated, thus "forcing" the commit
     * regardless of what properties have actually been changed. Use this method
     * sparingly.
     */
    public function forceCommit()
    {
        $this->markAllPropertiesAsMutated();
        $this->commit();
    }

    /**
     * Return a parameterized insert string ready for use in a prepared
     * statement.
     * 
     * @return mixed Parameterized insert string or FALSE if there are no
     * mutated properties
     */
    public function toParameterizedInsertString()
    {
        if(!$this->getMutatedPropertyArray())
            return FALSE;

        $table = static::getOriginTable();
        $where = static::constructParameterizedWhereClause((array) static::getPrimaryProperties());
        $mutatedProps = array_map(function($p){ return '`' . $this->schemaMap[$p] . '`'; }, $this->getMutatedPropertyArray());

        return "INSERT INTO `$table` "
            . '(' . implode(', ', $mutatedProps) . ') '
            . 'VALUES (' . str_repeat('?, ', count($mutatedProps) - 1) . '?)';
    }

    /**
     * Return a parameterized update string ready for use in a prepared
     * statement.
     * 
     * @return mixed Parameterized insert string or FALSE if there are no
     * mutated properties
     */
    public function toParameterizedUpdateString()
    {
        if(!$this->getMutatedPropertyArray())
            return FALSE;

        $table = static::getOriginTable();
        $where = static::constructParameterizedWhereClause((array) static::getPrimaryProperties());

        $parameterizedProps = array_map(
            (function($prop){ return '`' . $this->schemaMap[$prop] . '` = ?'; })->bindTo($this),
            $this->getMutatedPropertyArray()
        );

        return "UPDATE `$table` SET " . implode(', ', $parameterizedProps) . " WHERE $where LIMIT 1";
    }

    /**
     * Returns TRUE if this entity exists in the database backend, otherwise
     * FALSE.
     * 
     * @return bool
     */
    public function existsInBackend()
    {
        if($this->getUniqueId() === NULL || $this->getUniqueId() === FALSE)
            return FALSE;

        $table = static::getOriginTable();
        $where = static::constructParameterizedWhereClause((array) static::getPrimaryProperties());

        return (bool) static::query("SELECT count(*) as c FROM `$table` WHERE $where", (array) $this->getUniqueId())->fetch()->c;
    }

    /**
     * Returns the SQL table this entity actually belongs to.
     * 
     * @return string The origin table for this entity
     */
    abstract public static function getOriginTable();

    /**
     * Returns the primary key column name(s) this entity represents.
     * 
     * @return mixed A string of one or array of many primary key column names
     */
    abstract public static function getPrimaryProperties();

    /* XXX: from AbstractEntity: abstract public function getUniqueId(); */

    /* XXX: from AbstractEntity: abstract public static function getSchema(); */
}
