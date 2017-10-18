<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

use PHPUnit\Framework\TestCase;
use Application\ORM\Entity\AbstractEntity;

class __EntityTester extends AbstractEntity
{
    public function getUniqueId()
    {
        return 1;
    }

    public function commit(){}

    public static function getSchema()
    {
        return ['a', 'b', 'c', 'x', 'y', 'z', 'a_a', 'a_a_abc'];
    }
}

class __EntityTester2 extends AbstractEntity
{
    public function getUniqueId()
    {
        return $this->a;
    }

    public function commit(){}

    public static function getSchema()
    {
        return ['a', 'b', 'c', 'x', 'y', 'z'];
    }
}

class AbstractEntityTest extends TestCase
{
    private $entity;

    public function setUp()
    {
        $this->entity = new __EntityTester();
    }

    protected function assertArrayEquals(array $expected, array $actual, $message = '')
    {
        $this->assertEquals($expected, $actual, $message, 0.0, 10, TRUE);
    }

    public function testNewInstanceProvidedWithInitialDataIsNotMutated()
    {
        $this->entity = new __EntityTester(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertCount(0, $this->entity->getMutatedPropertyArray());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testNewInstanceProvidedWithBadInitialDataOk()
    {
        $data = ['fds' => 1, 'gfdghd' => 2, 'fafdaa' => 3];

        $this->entity = new __EntityTester($data);
        $this->assertArrayEquals($data, $this->entity->toArray());

        // XXX: should throw
        $this->entity->fds;
    }

    public function testSetDataResultsInPropertyMutations()
    {
        $this->entity->setData(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertArrayEquals(['a', 'b', 'c'], $this->entity->getMutatedPropertyArray());
    }

    public function testGettersAndSettersWorkAsExpected()
    {
        $data = ['a' => 1, 'b' => 2, 'c' => 3];

        $this->assertArrayEquals([], $this->entity->toArray());

        $this->entity->setData($data);

        $this->assertArrayEquals($data, $this->entity->toArray());

        $this->assertSame($data['a'], $this->entity->a);
        $this->assertSame($data['b'], $this->entity->b);
        $this->assertSame($data['c'], $this->entity->c);

        $this->entity->a = 9;
        $this->entity->b = 8;
        $this->entity->c = 7;

        $this->assertSame(9, $this->entity->a);
        $this->assertSame(8, $this->entity->b);
        $this->assertSame(7, $this->entity->c);
    }

    public function testUnderscoreConvertedGettersAndSettersWorkAsExpected()
    {
        $data = ['a_a' => 1, 'a_a_abc' => 2];

        $this->assertArrayEquals([], $this->entity->toArray());

        $this->entity->setData($data);

        $this->assertArrayEquals($data, $this->entity->toArray());

        $this->assertSame($data['a_a'], $this->entity->aA);
        $this->assertSame($data['a_a_abc'], $this->entity->aAAbc);
        $this->assertFalse(isset($this->entity->a_a));

        $this->entity->aA = 9;
        $this->entity->aAAbc = 8;

        $this->assertSame(9, $this->entity->aA);
        $this->assertSame(8, $this->entity->aAAbc);
        $this->assertFalse(isset($this->entity->a_a_abc));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testDataGetterThrowsExceptionIfBadProperty()
    {
        $this->entity->nope;
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testDataSetterThrowsExceptionIfBadProperty()
    {
        $this->entity->nope = 'bad';
    }

    public function testDataIssetWorksAsExpected()
    {
        $this->assertFalse(isset($this->entity->a));
        $this->entity->setData(['a' => 1]);
        $this->assertTrue(isset($this->entity->a));
    }

    public function testSetDataResetsDataAndPropertyMutations()
    {
        $data = ['x' => 9, 'y' => 8, 'z' => 7];

        $this->entity->setData(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->entity->setData($data);

        $this->assertArrayEquals(['x', 'y', 'z'], $this->entity->getMutatedPropertyArray(), 'Mutation reset failed.');
        $this->assertArrayEquals($data, $this->entity->toArray(), 'Data reset failed.');
    }

    public function testEqualsWorksAsExpected()
    {
        $ent1 = new __EntityTester(['a' => 5]);
        $ent2_1 = new __EntityTester2(['a' => 5]);
        $ent2_2 = new __EntityTester2(['a' => 5]);
        $ent2_3 = new __EntityTester2(['a' => 50]);

        $this->assertTrue($this->entity->equals($this->entity));
        $this->assertTrue($ent2_1->equals($ent2_1));
        $this->assertTrue($ent2_1->equals($ent2_2));
        $this->assertFalse($ent2_1->equals($ent2_3));
        $this->assertFalse($ent1->equals($ent2_1));
    }

    public function testToArrayWorksAsExpected()
    {
        $data = ['a' => 1, 'b' => 2, 'c' => 3];

        $this->entity->setData($data);
        $this->assertInternalType('array', $this->entity->toArray());
        $this->assertArrayEquals($data, $this->entity->toArray());
    }

    public function testToStringWorksAsExpected()
    {
        $ent = new __EntityTester2(['a' => 50]);
        $str = '<(__EntityTester2) uniqueid=50>';

        $this->assertSame($ent->toString(), $str);
        $this->assertSame((string) $ent, $str);
    }

    public function testGetDataPropertyWorksAsExpected()
    {
        $ent = new __EntityTester2(['a' => 50]);
        $this->assertEquals($ent->a, $ent->getDataProperty('a'));
    }
}
