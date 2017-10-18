<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

use PHPUnit\Framework\TestCase;
use Application\View\Helper\TitleHelper;

class TitleHelperTest extends TestCase
{
    private $titleHelper;

        public function setUp()
    {
        $this->titleHelper = new TitleHelper();
    }

    public function testGetSeparatorReturnsExpectedDefaultTitle()
    {
        $this->assertEquals($this->titleHelper->getSeparator(), ' · ');
    }

    public function testSetAndGetSeparatorBehaveExpectedly()
    {
        $EXPECTED_VAL = 'V';
        $this->titleHelper->setSeparator($EXPECTED_VAL);
        $this->assertEquals($this->titleHelper->getSeparator(), $EXPECTED_VAL);
    }

    public function testBuildTitleReturnsExpectedTitle()
    {
        $EXPECTED_VAL1 = 'A';
        $EXPECTED_VAL2 = 'AVB';
        $EXPECTED_VAL3 = 'BVA';

        $this->titleHelper2 = new TitleHelper();
        $this->titleHelper3 = new TitleHelper();

        $this->titleHelper->setSeparator('V');
        $this->titleHelper2->setSeparator('V');
        $this->titleHelper3->setSeparator('V');

        $this->titleHelper->append('A');
        $this->titleHelper2->append('A');
        $this->titleHelper2->append('B');
        $this->titleHelper3->append('A');
        $this->titleHelper3->prepend('B');

        $this->assertEquals($this->titleHelper->buildTitle(), $EXPECTED_VAL1);
        $this->assertEquals($this->titleHelper2->buildTitle(), $EXPECTED_VAL2);
        $this->assertEquals($this->titleHelper3->buildTitle(), $EXPECTED_VAL3);
    }

    public function testReturnsExpectedTitleWhenInvoked()
    {
        $this->titleHelper->setSeparator('V');
        $this->titleHelper->append('A');

        $cb = $this->titleHelper;
        $this->assertEquals($this->titleHelper->buildTitle(), $cb());
    }
}
