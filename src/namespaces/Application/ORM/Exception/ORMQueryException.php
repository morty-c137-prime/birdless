<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

namespace Application\ORM\Exception;

/**
 * Represents a failure while attempting to execute a query. The attempted query
 * (in its prepared form if applicable) is included with this exception.
 */
class ORMQueryException extends ORMGenericException
{
    protected $queryString;
    protected $code;

    public function __construct(string $queryString, \Throwable $previous)
    {
        parent::__construct(
            $previous->getMessage() . ". Full query: $queryString",
            (int) $previous->getCode(),
            $previous
        );

        $this->queryString = $queryString;
        $this->code = $previous->getCode();
    }

    public final function getQueryString()
    {
        return $this->queryString;
    }
}
