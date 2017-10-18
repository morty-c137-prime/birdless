<?php
/**
 * @author Xunnamius <me@xunn.io>
 * 
 * This class will decide what permissions an entity can apply to a well-defined
 * supervised resource or object.
 */

namespace Application\Auth;

class AclSupervisor
{
    /**
     * Accepts configuration and returns a properly configured AclSupervisor
     * object. Does not return a singleton.
     *
     * @example
     * $config = [
     *     permissions => [
     *         permissionUniqueIdentifier => [
     *             name => ...,
     *             description => ...,
     *         ],
     *     ],
     *     entityPriority => [
     *         priorityInt => entityUniqueIdentifier,
     *         ...
     *     ],
     *     supervised => [
     *         supervisedUniqueIdentifier => [
     *             AclSupervisor::DEFAULT_PERMS => [
     *                 permissionUniqueIdentifier => AclSupervisor::ALLOW|AclSupervisor::DENY|AclSupervisor::INHERIT
     *             ],
     *             entityUniqueIdentifier => [
     *                 permissionUniqueIdentifier => AclSupervisor::ALLOW|AclSupervisor::DENY|AclSupervisor::INHERIT
     *             ],
     *         ],
     *     ]
     * ]
     * 
     * @return AclSupervisor
     */
    public static function fromConfigString()
    {

    }

    /**
     * Accepts a PDO object and returns a properly configured AclSupervisor
     * object. Does not return a singleton. Much more memory-efficient than the
     * string configuration version for larger configurations.
     *
     * The DB schema must be properly set up before using PDO for ACL.
     * 
     * @return AclSupervisor
     */
    public static function fromPdoObject()
    {

    }
}
