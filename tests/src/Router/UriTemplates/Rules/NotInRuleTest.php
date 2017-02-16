<?php
namespace Opulence\Router\UriTemplates\Rules;

/**
 * Tests the not-in-array rule
 */
class NotInRuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that the correct slug is returned
     */
    public function testCorrectSlugIsReturned() : void
    {
        $this->assertEquals('notIn', (new NotInRule([1, 2]))->getSlug());
    }
    
    /**
     * Tests that a value in the array fails
     */
    public function testValueInArrayFails() : void
    {
        $rule = new NotInRule([1, 2, 3]);
        $this->assertFalse($rule->passes(1));
        $this->assertFalse($rule->passes(2));
        $this->assertFalse($rule->passes(3));
    }
    
    /**
     * Tests that a value not in the array passes
     */
    public function testValueNotInArrayPasses() : void
    {
        $rule = new NotInRule([1, 2, 3]);
        $this->assertTrue($rule->passes(4));
        $this->assertTrue($rule->passes(5));
        $this->assertTrue($rule->passes(6));
    }
}
