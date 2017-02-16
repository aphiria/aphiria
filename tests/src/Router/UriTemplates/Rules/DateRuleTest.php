<?php
namespace Opulence\Router\UriTemplates\Rules;

use DateTime;

/**
 * Tests the date rule
 */
class DateRuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that the correct slug is returned
     */
    public function testCorrectSlugIsReturned() : void
    {
        $this->assertEquals('date', (new DateRule('F j'))->getSlug());
    }
    
    /**
     * Tests that a single failing format
     */
    public function testFailingSingleFormat() : void
    {
        $format = 'F j';
        $rule = new DateRule($format);
        $this->assertFalse($rule->passes((new DateTime)->format('Ymd')));
    }
    
    /**
     * Tests that multiple failing formats
     */
    public function testFailingMultipleFormats() : void
    {
        $format1 = 'F j';
        $format2 = 'j F';
        $rule = new DateRule([$format1, $format2]);
        $this->assertFalse($rule->passes((new DateTime)->format('Ymd')));
        $this->assertFalse($rule->passes((new DateTime)->format('Ymd')));
    }
    
    /**
     * Tests that a single passing format
     */
    public function testPassingSingleFormat() : void
    {
        $format = 'F j';
        $rule = new DateRule($format);
        $this->assertTrue($rule->passes((new DateTime)->format($format)));
    }
    
    /**
     * Tests that multiple passing formats
     */
    public function testPassingMultipleFormats() : void
    {
        $format1 = 'F j';
        $format2 = 'j F';
        $rule = new DateRule([$format1, $format2]);
        $this->assertTrue($rule->passes((new DateTime)->format($format1)));
        $this->assertTrue($rule->passes((new DateTime)->format($format2)));
    }
}
