<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

use PHPUnit\Framework\TestCase;
use Application\ORM\Database\WritablePDORow;

class ReadOnlyObject
{
    public function __get($property)
    {
        return TRUE;
    }

    public function __isset($property)
    {
        return TRUE;
    }

    public function __set($property, $value)
    {
        throw new \RunetimeException('This object is read only!');
    }
}

class WritablePDORowTest extends Testcase
{
    private $row;

    public function setUp()
    {
        $this->row = new WritablePDORow(new ReadOnlyObject());
    }

    public function testCanReadAndWriteProperly()
    {
        $this->assertTrue($this->row->a);
        $this->row->a = 5;
        $this->assertEquals(5, $this->row->a);
    }

    public function testDoesIssetProperly()
    {
        $this->assertTrue(isset($this->row->a));
    }
}
