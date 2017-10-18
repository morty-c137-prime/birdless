<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

namespace Application\ORM\Exception;

/**
 * Represents a failure while attempting to read in and/or locate an entity.
 */
class ORMEntityNotFoundException extends ORMGenericException
{
    protected $id;
    protected $class;

    public function __construct($class, $id, \Throwable $previous = NULL)
    {
        $this->class = $class;
        $this->id = implode(',', (array) $id);
        $msg = "Entity $class (ident={$this->id}) not found";
        
        if($previous)
            parent::__construct($msg, $previous->getCode(), $previous);

        else
            parent::__construct($msg);
    }

    public final function getEntityId()
    {
        return $this->id;
    }

    public final function getEntityClass()
    {
        return $this->class;
    }
}
