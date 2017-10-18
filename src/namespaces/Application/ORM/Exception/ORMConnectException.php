<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

namespace Application\ORM\Exception;

/**
 * Represents a failure (typically raised by PDO) encountered while trying to
 * connect to something—typically a backend database of some type.
 */
class ORMConnectException extends ORMGenericException
{
    public function __construct(\Throwable $previous)
    {
        parent::__construct($previous->getMessage(), $previous->getCode(), $previous);
    }
}
