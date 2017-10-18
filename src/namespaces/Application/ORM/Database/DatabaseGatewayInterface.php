<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

namespace Application\ORM\Database;

/**
 * An interface for a general database adapter which itself is generally a part
 * of the larger ORM abstraction layer.
 */
interface DatabaseGatewayInterface
{
    public function connect($pdo = null);
    public function getPDOObject();
    public function disconnect();
}
