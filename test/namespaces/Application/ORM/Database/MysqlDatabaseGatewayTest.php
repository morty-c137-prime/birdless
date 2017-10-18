<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

use Application\ORM\Database\MysqlDatabaseGateway;
use DarkTools\Extensions\PHPUnit\DbUnit\AbstractGatewayTester;
use Application\ORM\Exception\{ORMGenericException,ORMConnectException,ORMQueryException};

class MysqlDatabaseGatewayTest extends AbstractGatewayTester
{
    protected static $pdo;
    protected $mysql;

    public function getConnection()
    {
        parent::getConnection();
        $this->mysql = new MysqlDatabaseGateway();

        return $this->conn;
    }
    
    public function testGetDbObjectReturnsDbObject()
    {
        $this->assertTrue($this->mysql->getPDOObject() instanceof \PDO);
    }

    public function testCanConnectToDb()
    {
        $this->assertFalse($this->mysql->isConnected);
        $this->assertFalse($this->mysql->connected);
        $this->mysql->connect();
        $this->assertTrue($this->mysql->isConnected);
        $this->assertTrue($this->mysql->connected);
        $this->mysql->disconnect();
        $this->assertFalse($this->mysql->connected);
        $this->assertFalse($this->mysql->isConnected);
    }

    public function testCallingConnectWhenAlreadyConnectedDoesNothing()
    {
        // TODO: Mock PDO for this one?
        $this->mysql->connect();
        $this->mysql->connect();
        $this->assertTrue($this->mysql->isConnected);
    }

    public function testQueryReturnsPDOStatement()
    {
        $this->assertEquals(2, $this->mysql->query('SELECT 1+1 as a')->fetch()->a);
    }

    public function testParameterizedQueryReturnsPDOStatement()
    {
        $this->mysql->connect(static::$pdo);
        $this->assertEquals(
            'test_user_2',
            $this->mysql->query('SELECT username FROM users WHERE user_id = ?', [2])->fetch()->username
        );
    }

    public function testGetLastInsertIdWorksAsExpected()
    {
        $this->mysql->connect(static::$pdo);
        $this->assertEquals(2, $this->mysql->getLastInsertId());
    }

    public function testQueryThrowsORMQueryExceptionOnBadSQL()
    {
        $this->expectException(ORMQueryException::class);
        $this->mysql->query('bad sql is here');
    }
}
