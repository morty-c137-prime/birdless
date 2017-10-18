<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

use Application\ORM\Entity\EntityInterface;
use Application\ORM\Entity\AbstractEntityStaticEntityGateway;
use Application\ORM\Database\DatabaseGatewayInterface;
use Application\ORM\Database\MysqlDatabaseGateway;
use DarkTools\Extensions\PHPUnit\DbUnit\AbstractGatewayTester;

class _EntityGatewayTester extends AbstractEntityStaticEntityGateway
{
    protected static $databaseGateway;

    public function getUniqueId()
    {
        return [$this->a, $this->b];
    }

    public static function getSchema()
    {
        return ['a', 'b', 'a_b'];
    }

    public static function getOriginTable(){ return '_ent_gw_test'; }
    public static function getPrimaryProperties(){ return ['a', 'b']; }
}

class _EntityGatewayTester2 extends AbstractEntityStaticEntityGateway
{
    protected static $databaseGateway;

    public function getUniqueId()
    {
        return $this->a;
    }

    public static function getSchema()
    {
        return ['a', 'a_b'];
    }

    public static function getOriginTable(){ return '_ent_gw_test'; }
    public static function getPrimaryProperties(){ return 'a'; }
}

class _EntityGatewayTester3 extends AbstractEntityStaticEntityGateway
{
    protected static $databaseGateway;

    public function __get($property)
    {
        $val = parent::__get($property);

        if(!($val instanceof EntityInterface))
        {
            switch($property)
            {
                case 'aB':
                    $val = $this->{$property} = _EntityGatewayTester2::fromUniqueId($val);
                    break;
            }
        }

        return $val;
    }

    public function getUniqueId()
    {
        return $this->a;
    }

    public static function getSchema()
    {
        return ['a', 'a_b'];
    }

    public static function getOriginTable(){ return '_ent_gw_test'; }
    public static function getPrimaryProperties(){ return 'a'; }
}

class AbstractEntityStaticEntityGatewayTest extends AbstractGatewayTester
{
    protected static $pdo;

    public function getConnection()
    {
        parent::getConnection();

        static::$pdo->query('DROP TABLE IF EXISTS _ent_gw_test');
        static::$pdo->query('CREATE TEMPORARY TABLE _ent_gw_test (a INT NOT NULL PRIMARY KEY, b INT NOT NULL, a_b INT NOT NULL)');
        static::$pdo->query('INSERT INTO _ent_gw_test VALUES (5, 10, 5), (999, 10, 5)');

        return $this->conn;
    }

    protected function setDBGateway()
    {
        _EntityGatewayTester::setDatabaseGateway(new MysqlDatabaseGateway(static::$pdo));
        _EntityGatewayTester2::setDatabaseGateway(new MysqlDatabaseGateway(static::$pdo));
        _EntityGatewayTester3::setDatabaseGateway(new MysqlDatabaseGateway(static::$pdo));
    }

    public function testFromUniqueIdWorksAsExpected()
    {
        $this->setDBGateway();

        $entity1 = _EntityGatewayTester::fromUniqueId([5, 10]);
        $entity2 = _EntityGatewayTester2::fromUniqueId(5);

        $this->assertEquals([5, 10], $entity1->getUniqueId());
        $this->assertEquals(5, $entity1->aB);
        $this->assertEquals(5, $entity2->getUniqueId());
        $this->assertEquals(5, $entity2->aB);
    }

    public function testExistsInBackendWorksAsExpected()
    {
        $this->setDBGateway();

        $entity1 = _EntityGatewayTester::fromUniqueId([5, 10]);

        $this->assertFalse((new _EntityGatewayTester())->existsInBackend());
        $this->assertTrue($entity1->existsInBackend());
    }

    public function testCommitWorksAsExpected()
    {
        $this->setDBGateway();
        
        $entity1 = _EntityGatewayTester::fromUniqueId([5, 10]);

        $this->assertFalse(_EntityGatewayTester::commitEntity($entity1));

        $entity1->aB = 100;

        $this->assertTrue(_EntityGatewayTester::commitEntity($entity1));
        $this->assertEquals($entity1->aB, _EntityGatewayTester::fromUniqueId([5, 10])->aB);

        $entity1->aB = 6543;
        $entity1->commit();

        $this->assertEquals($entity1->aB, _EntityGatewayTester::fromUniqueId([5, 10])->aB);
    }

    public function testForceCommitNewEntityWorksAsExpected()
    {
        $this->setDBGateway();

        $entity2 = new _EntityGatewayTester2(['a' => 777, 'a_b' => 666]);
        $this->assertFalse($entity2->existsInBackend());
        $entity2->forceCommit();
        $this->assertTrue($entity2->existsInBackend());
        $this->assertTrue($entity2->equals(_EntityGatewayTester2::fromUniqueId(777)));
    }

    public function testGetAndSetDatabaseGateway()
    {
        $gw = new MysqlDatabaseGateway(static::$pdo);
        _EntityGatewayTester::setDatabaseGateway($gw);

        $this->assertSame($gw, _EntityGatewayTester::getDatabaseGateway());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFromUniqueIdThrowsExceptionWithBadId()
    {
        _EntityGatewayTester::fromUniqueId(5);
    }

    public function testToParameterizedStringWorksAsExpected()
    {
        $this->setDBGateway();

        $entity1 = _EntityGatewayTester::fromUniqueId([5, 10]);
        $entity2 = _EntityGatewayTester2::fromUniqueId(5);

        $this->assertFalse($entity1->toParameterizedInsertString());
        $this->assertFalse($entity1->toParameterizedUpdateString());

        $entity1->a = 100;

        $this->assertEquals(
            'INSERT INTO `_ent_gw_test` (`a`) VALUES (?)',
            $entity1->toParameterizedInsertString()
        );

        $this->assertEquals(
            'UPDATE `_ent_gw_test` SET `a` = ? WHERE `a` = ? AND `b` = ? LIMIT 1',
            $entity1->toParameterizedUpdateString()
        );

        $entity2->a = 250;
        $entity2->aB = 500;

        $this->assertEquals(
            'INSERT INTO `_ent_gw_test` (`a`, `a_b`) VALUES (?, ?)',
            $entity2->toParameterizedInsertString()
        );

        $this->assertEquals(
            'UPDATE `_ent_gw_test` SET `a` = ?, `a_b` = ? WHERE `a` = ? LIMIT 1',
            $entity2->toParameterizedUpdateString()
        );
    }

    public function testSubEntitiesAreSupportedNatively()
    {
        $this->setDBGateway();

        $entity3 = _EntityGatewayTester3::fromUniqueId(5);
        $this->assertFalse($entity3->commit());
        $this->assertInstanceOf(_EntityGatewayTester2::class, $entity3->aB);
        $this->assertSame($entity3->aB, $entity3->aB);
        $this->assertTrue($entity3->commit());
        $entity3->aB = 999;
        $this->assertTrue($entity3->commit());
        $this->assertTrue($entity3->aB->equals((_EntityGatewayTester3::fromUniqueId(5))->aB));
        $entity3->aB = 5;
        $this->assertEquals(5, $entity3->getDataProperty('aB'));
        $this->assertTrue($entity3->commit());
        $this->assertEquals($entity3->getDataProperty('aB'), (_EntityGatewayTester3::fromUniqueId(5))->getDataProperty('aB'));
        $this->assertInstanceOf(_EntityGatewayTester2::class, $entity3->aB);
    }
}
