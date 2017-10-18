<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

use Application\ORM\Entity\User;
use Application\ORM\Database\MysqlDatabaseGateway;
use DarkTools\Extensions\PHPUnit\DbUnit\AbstractGatewayTester;

class RelationsGraphTest extends AbstractGatewayTester
{
    protected static $pdo;

    public function getConnection()
    {
        parent::getConnection();
        User::setDatabaseGateway(new MysqlDatabaseGateway(static::$pdo));

        return $this->conn;
    }

    public function getDataSet($tag = 'init-seed', $prefix = 'test/db/bdpa_hscc_test-')
    {
        return parent::getDataSet('relationsgraph-seed');
    }

    public function testGetRelationTypesReturnsProperAbstractRelationTypesInstance()
    {
        $relations = User::getRelationTypes();

        $this->assertInternalType('array', $relations::toArray());
        $this->assertCount(3, $relations::toArray());
        $this->assertInternalType('string', $relations::getDescriptionFor($relations::GUARDIAN));
    }

    public function testGetRelationTypesWorksAsExpected()
    {
        $relations = User::getRelationTypes();

        $this->assertArrayEquals(['guardian' => 1, 'sibling' => 2, 'lolwut' => 3], $relations::toArray());
        $this->assertEquals('The parent or guardian of a member.', $relations::getDescriptionFor($relations::GUARDIAN));
        $this->assertEquals('', $relations::getDescriptionFor($relations::SIBLING));
        $this->assertEquals("I don't know don't ask me!", $relations::getDescriptionFor($relations::LOLWUT));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetDescriptionForThrowsExceptionOnBadId()
    {
        User::getRelationTypes()::getDescriptionFor(500);
    }

    public function testAddRelationTypeByNameWorksAsExpected()
    {
        User::addRelationTypeByName('newtype');
        $this->assertArrayEquals(['guardian' => 1, 'sibling' => 2, 'lolwut' => 3, 'newtype' => 4], User::getRelationTypes()::toArray());

        User::addRelationTypeByName('newtype2', 'description!');

        $relations = User::getRelationTypes();

        $this->assertEquals('', $relations::getDescriptionFor($relations::NEWTYPE));
        $this->assertEquals('description!', $relations::getDescriptionFor($relations::NEWTYPE2));
    }

    public function testRemoveRelationTypeWorksAsExpected()
    {
        User::removeRelationType(User::getRelationTypes()::GUARDIAN);

        $this->assertArrayEquals(['sibling' => 2, 'lolwut' => 3], User::getRelationTypes()::toArray());

        $this->assertEquals([
            2 => (object) ['origin' => 1, 'terminal' => 3, 'type' => 2, 'confirmed' => 0],
            3 => (object) ['origin' => 3, 'terminal' => 1, 'type' => 3, 'confirmed' => 1],
        ], User::fromUsername('test_user_1')->getAllRelations());
    }

    public function testAddAndRemoveRelationTypeWorksDespiteCase()
    {
        User::addRelationTypeByName('NeWTYpe');
        User::removeRelationType(User::getRelationTypes()::GUARDIAN);

        $this->assertArrayEquals(['newtype' => 4, 'sibling' => 2, 'lolwut' => 3], User::getRelationTypes()::toArray());
    }

    public function testAddAndRemoveRelationTypeWorksIfCalledMultipleTimes()
    {
        User::addRelationTypeByName('newtype');
        User::addRelationTypeByName('newtype', 'description!');
        User::addRelationTypeByName('nEwTyPE', 'description');

        $relationTypes = User::getRelationTypes();

        $this->assertArrayEquals(['guardian' => 1, 'sibling' => 2, 'lolwut' => 3, 'newtype' => 4], $relationTypes::toArray());
        $this->assertEquals('', $relationTypes::getDescriptionFor($relationTypes::NEWTYPE));
        
        User::removeRelationType($relationTypes::NEWTYPE);
        User::removeRelationType($relationTypes::NEWTYPE);

        $this->assertArrayEquals(['guardian' => 1, 'sibling' => 2, 'lolwut' => 3], User::getRelationTypes()::toArray());
    }

    public function testGetAllRelationsWorksAsExpected()
    {
        $user1 = User::fromUsername('test_user_1');
        $user2 = User::fromUsername('test_user_2');

        $this->assertEquals([
            1 => (object) ['origin' => 1, 'terminal' => 2, 'type' => 1, 'confirmed' => 1],
            2 => (object) ['origin' => 1, 'terminal' => 3, 'type' => 2, 'confirmed' => 0],
            3 => (object) ['origin' => 3, 'terminal' => 1, 'type' => 3, 'confirmed' => 1],
        ], $user1->getAllRelations());

        $this->assertEquals([
            1 => (object) ['origin' => 1, 'terminal' => 2, 'type' => 1, 'confirmed' => 1],
        ], $user1->getAllRelationsWith($user2));

        $this->assertEquals([
            1 => (object) ['origin' => 1, 'terminal' => 2, 'type' => 1, 'confirmed' => 1],
            4 => (object) ['origin' => 2, 'terminal' => 4, 'type' => 3, 'confirmed' => 0],
        ], $user2->getAllRelations());

        $this->assertEquals([
            1 => (object) ['origin' => 1, 'terminal' => 2, 'type' => 1, 'confirmed' => 1],
        ], $user2->getAllRelationsWith($user1));
    }

    public function testAddRelationWithWorksAsExpected()
    {
        $relationTypes = User::getRelationTypes();
        $user1 = User::fromUsername('test_user_1');
        $user4 = User::fromUsername('test_user_4');

        $this->assertEquals(5, $user1->addRelationWith($user4, $relationTypes::GUARDIAN));

        $this->assertEquals([
            5 => (object) ['origin' => 1, 'terminal' => 4, 'type' => 1, 'confirmed' => 0],
        ], $user1->getAllRelationsWith($user4));

        $this->assertEquals([
            5 => (object) ['origin' => 1, 'terminal' => 4, 'type' => 1, 'confirmed' => 0],
        ], $user4->getAllRelationsWith($user1));
    }

    public function testRemoveRelationWithWorksAsExpected()
    {
        $relationTypes = User::getRelationTypes();
        $user1 = User::fromUsername('test_user_1');
        $user2 = User::fromUsername('test_user_2');
        $user4 = User::fromUsername('test_user_4');

        $user1->addRelationWith($user4, $relationTypes::GUARDIAN);
        $user1->removeRelationWith($user4, $relationTypes::GUARDIAN);
        $user1->removeRelationWith($user2, $relationTypes::GUARDIAN);

        $this->assertEquals([
            2 => (object) ['origin' => 1, 'terminal' => 3, 'type' => 2, 'confirmed' => 0],
            3 => (object) ['origin' => 3, 'terminal' => 1, 'type' => 3, 'confirmed' => 1],
        ], $user1->getAllRelations());

        $this->assertEquals([], $user2->getAllRelationsWith($user1));
        $this->assertEquals([], $user4->getAllRelationsWith($user1));
    }

    public function testGetAllRelationsReturnsEmptyIfAllRelationsRemoved()
    {
        $relationTypes = User::getRelationTypes();
        $user1 = User::fromUsername('test_user_1');
        $user2 = User::fromUsername('test_user_2');
        $user3 = User::fromUsername('test_user_3');

        $user1->removeRelationWith($user2, $relationTypes::GUARDIAN);
        $user1->removeRelationWith($user3, $relationTypes::SIBLING);
        $user3->removeRelationWith($user1, $relationTypes::LOLWUT);

        $this->assertEquals([], $user1->getAllRelations());
    }

    public function testGetAllRelationsWithReturnsEmptyIfNoRelationsWith()
    {
        $user1 = User::fromUsername('test_user_1');
        $user4 = User::fromUsername('test_user_4');

        $this->assertEquals([], $user1->getAllRelationsWith($user4));
    }

    public function testAddAndRemoveRelationWithWorksIfCalledMultipleTimes()
    {
        $user1 = User::fromUsername('test_user_1');
        $user2 = User::fromUsername('test_user_2');

        $relationTypes = User::getRelationTypes();

        $user1->removeRelationWith($user2, $relationTypes::GUARDIAN);
        $user1->removeRelationWith($user2, $relationTypes::GUARDIAN);
        $user1->removeRelationWith($user2, $relationTypes::GUARDIAN);

        $this->assertEquals([
            2 => (object) ['origin' => 1, 'terminal' => 3, 'type' => 2, 'confirmed' => 0],
            3 => (object) ['origin' => 3, 'terminal' => 1, 'type' => 3, 'confirmed' => 1],
        ], $user1->getAllRelations());

        $this->assertEquals(5, $user1->addRelationWith($user2, $relationTypes::SIBLING));
        $this->assertEquals(5, $user1->addRelationWith($user2, $relationTypes::SIBLING));
        $this->assertEquals(5, $user1->addRelationWith($user2, $relationTypes::SIBLING));

        $this->assertEquals([
            5 => (object) ['origin' => 1, 'terminal' => 2, 'type' => 2, 'confirmed' => 0],
            2 => (object) ['origin' => 1, 'terminal' => 3, 'type' => 2, 'confirmed' => 0],
            3 => (object) ['origin' => 3, 'terminal' => 1, 'type' => 3, 'confirmed' => 1],
        ], $user1->getAllRelations());
    }

    public function testGetRelationWorksAsExpected()
    {
        $this->assertEquals(
            (object) ['origin' => 1, 'terminal' => 2, 'type' => 1, 'confirmed' => 1],
            User::getRelation(1)
        );
    }

    public function testConfirmRelationWorksAsExpected()
    {
        User::confirmRelation(1);

        $this->assertEquals(
            (object) ['origin' => 1, 'terminal' => 2, 'type' => 1, 'confirmed' => 1],
            User::getRelation(1)
        );

        $this->assertEquals(
            (object) ['origin' => 1, 'terminal' => 3, 'type' => 2, 'confirmed' => 0],
            User::getRelation(2)
        );

        User::confirmRelation(2);

        $this->assertEquals(
            (object) ['origin' => 1, 'terminal' => 3, 'type' => 2, 'confirmed' => 1],
            User::getRelation(2)
        );
    }

    public function testRemoveRelationWorksAsExpected()
    {
        User::removeRelation(1);
        $this->assertEquals(NULL, User::getRelation(1));
    }

    public function testGetConfirmedRelationsWorksAsExpected()
    {
        $user1 = User::fromUsername('test_user_1');

        $this->assertEquals([
            1 => (object) ['origin' => 1, 'terminal' => 2, 'type' => 1, 'confirmed' => 1],
            3 => (object) ['origin' => 3, 'terminal' => 1, 'type' => 3, 'confirmed' => 1],
        ], $user1->getConfirmedRelations());
    }

    public function testGetUnconfirmedRelationsWorksAsExpected()
    {
        $user1 = User::fromUsername('test_user_1');

        $this->assertEquals([
            2 => (object) ['origin' => 1, 'terminal' => 3, 'type' => 2, 'confirmed' => 0],
        ], $user1->getUnconfirmedRelations());
    }

    public function testGetConfirmedRelationsWithWorksAsExpected()
    {
        $user1 = User::fromUsername('test_user_1');
        $user3 = User::fromUsername('test_user_3');

        $this->assertEquals([
            3 => (object) ['origin' => 3, 'terminal' => 1, 'type' => 3, 'confirmed' => 1],
        ], $user1->getConfirmedRelationsWith($user3));

        $this->assertEquals([
            3 => (object) ['origin' => 3, 'terminal' => 1, 'type' => 3, 'confirmed' => 1],
        ], $user3->getConfirmedRelationsWith($user1));
    }

    public function testGetUnconfirmedRelationsWithWorksAsExpected()
    {
        $user1 = User::fromUsername('test_user_1');
        $user3 = User::fromUsername('test_user_3');

        $this->assertEquals([
            2 => (object) ['origin' => 1, 'terminal' => 3, 'type' => 2, 'confirmed' => 0],
        ], $user1->getUnconfirmedRelationsWith($user3));

        $this->assertEquals([
            2 => (object) ['origin' => 1, 'terminal' => 3, 'type' => 2, 'confirmed' => 0],
        ], $user3->getUnconfirmedRelationsWith($user1));
    }
}
