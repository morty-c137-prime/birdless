<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

namespace Application\ORM\Entity;

/**
 * Represents an ORM entity.
 */
interface EntityInterface
{
    public function getUniqueId();
    public function getMutatedPropertyArray();
    public function clearMutatedPropertyArray();
    public function setData($data);
    public function commit();
    public function equals(EntityInterface $entity);
    public function toArray();
    public function toString();
    public function __toString();
}
