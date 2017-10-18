<?php
/**
 * @author Xunnamius <me@xunn.io>
 * 
 * Defines an interface that should be implemented by any entity being supervised
 * by an AclSupervisor.
 */

namespace Application\Auth;

interface AclSupervisedInterface
{
    public function GetAclUniqueIdentifier();
}
