<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

use PHPUnit\Framework\TestCase;
use Application\View\Helper\CacheBuster;

class CacheBusterTest extends TestCase
{
    private $cacheBuster;

        public function setUp()
    {
        $this->cacheBuster = new CacheBuster();
    }

    public function testGetBustFunctionReturnsExpectedDefaultFn()
    {
        $expectedBustFn = CacheBuster::getDefaultBustFunction();
        $actualBustFn = $this->cacheBuster->getBustFunction();

        $this->assertEquals($expectedBustFn('index.php'), $actualBustFn('index.php'));
    }

    public function testGetAndSetBustFunctionsDoExpected()
    {
        $EXPECTED_VAL = 'stub';
        $bustfn = function() use ($EXPECTED_VAL){ return $EXPECTED_VAL; };

        $this->cacheBuster->setBustFunction($bustfn);
        $this->assertEquals($EXPECTED_VAL, $this->cacheBuster->getBustFunction()($EXPECTED_VAL));
    }

    public function testBustPathReturnsExpectedPath()
    {
        $EXPECTED_VAL = 'stub';
        $bustfn = function() use ($EXPECTED_VAL) { return $EXPECTED_VAL; };

        $this->cacheBuster->setBustFunction($bustfn);
        $this->assertEquals($EXPECTED_VAL, $this->cacheBuster->bustPath($EXPECTED_VAL));
    }

    public function test__invokeReturnsExpectedPath()
    {
        $EXPECTED_VAL = 'stub';
        $bustfn = function() use ($EXPECTED_VAL) { return $EXPECTED_VAL; };

        $this->cacheBuster->setBustFunction($bustfn);
        $cb = $this->cacheBuster;
        
        $this->assertEquals($EXPECTED_VAL, $cb($EXPECTED_VAL));
    }
}
