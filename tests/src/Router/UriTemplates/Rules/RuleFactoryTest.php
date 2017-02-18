<?php
namespace Opulence\Router\UriTemplates\Rules;

use InvalidArgumentException;
use RuntimeException;

/**
 * Tests the rule factory
 */
class RuleFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var RuleFactory The rule factory to use in tests */
    private $ruleFactory = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->ruleFactory = new RuleFactory();
    }

    /**
     * Tests that a closure that does not return a rule instance throws an exception
     */
    public function testClosureThatDoesNotReturnRuleInstanceThrowsException() : void
    {
        $this->expectException(RuntimeException::class);
        $factory = function () {
            return [];
        };
        $this->ruleFactory->registerRuleFactory('foo', $factory);
        $this->ruleFactory->createRule('foo');
    }

    /**
     * Tests that creating a rule without a registered factory throws an exception
     */
    public function testCreatingRuleWithNoFactoryRegisteredThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->ruleFactory->createRule('foo');
    }

    /**
     * Tests that a factory that does not take in parameters returns a rule instance
     */
    public function testFactoryThatDoesNotTakeParametersReturnsRuleInstance() : void
    {
        $expectedRule = $this->createMock(IRule::class);
        $factory = function () use ($expectedRule) {
            return $expectedRule;
        };
        $this->ruleFactory->registerRuleFactory('foo', $factory);
        $this->assertSame($expectedRule, $this->ruleFactory->createRule('foo'));
    }

    /**
     * Tests that a factory that takes in parameters returns a rule instance
     */
    public function testFactoryThatTakesParametersReturnsRuleInstance() : void
    {
        $expectedRule = $this->createMock(IRule::class);
        $factory = function ($foo, $bar) use ($expectedRule) {
            $this->assertEquals(1, $foo);
            $this->assertEquals(2, $bar);

            return $expectedRule;
        };
        $this->ruleFactory->registerRuleFactory('foo', $factory);
        $this->assertSame($expectedRule, $this->ruleFactory->createRule('foo', [1, 2]));
    }
}
