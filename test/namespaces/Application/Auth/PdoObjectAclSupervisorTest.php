<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

use PHPUnit\Framework\TestCase;
use Application\Auth\AclSupervisedInterface;
use Application\ORM\Database\MysqlDatabaseGateway;
use DarkTools\Extensions\PHPUnit\DbUnit\AbstractGatewayTester;

class PdoObjectAclSupervisorTest extends AbstractGatewayTester
{
    private $acl;
    protected static $pdo;

    public function getConnection()
    {
        parent::getConnection();

        $this->acl = new PdoObjectAclSupervisor(new MysqlDatabaseGateway(static::$pdo));

        return $this->conn;
    }

    public function getDataSet($tag = 'init-seed', $prefix = 'test/db/bdpa_hscc_test-')
    {
        return parent::getDataSet('acl-seed');
    }
}
