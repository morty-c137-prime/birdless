<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

namespace Application\ORM\Entity;

use Application\ORM\Database\DatabaseGatewayInterface;
use Application\ORM\Entity\EntityInterface;

interface StaticEntityGatewayInterface
{
    public static function getDatabaseGateway();
    public static function setDatabaseGateway(DatabaseGatewayInterface $gateway);
    public static function fromUniqueId($id);
    public static function commitEntity(EntityInterface $entity);
}
