<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

namespace Application\ORM\Exception;

/**
 * Represents a generic exception in the ORM layer.
 */
class ORMGenericException extends \RuntimeException
{
    protected $code;

    public function __construct($message = NULL, $code = NULL, \Throwable $previous = NULL)
    {
        $message = $message ?? ($previous ? $previous->getMessage() : NULL);
        $code = $code ?? ($previous ? $previous->getCode() : NULL);

        $this->code = $code;

        parent::__construct($message, $code, $previous);
    }
}
