<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

use PHPUnit\Framework\TestCase;
use Application\Auth\AclSupervisedInterface;

class ConfigStringAclSupervisorTest extends TestCase
{
    private $acl;

    public function setUp()
    {
        $this->acl = new ConfigStringAclSupervisor();
    }
}
