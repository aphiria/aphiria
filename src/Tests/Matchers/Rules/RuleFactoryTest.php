<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\Matchers\Rules;

use InvalidArgumentException;
use RuntimeException;
use Opulence\Routing\Matchers\Rules\IRule;
use Opulence\Routing\Matchers\Rules\RuleFactory;
use PHPUnit\Framework\TestCase;

/**
 * Tests the rule factory
 */
class RuleFactoryTest extends TestCase
{
    /** @var RuleFactory The rule factory to use in tests */
    private $ruleFactory;

    public function setUp(): void
    {
        $this->ruleFactory = new RuleFactory();
    }

    public function testClosureThatDoesNotReturnRuleInstanceThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Factory for rule "foo" does not return an instance of IRule');
        $factory = function () {
            return [];
        };
        $this->ruleFactory->registerRuleFactory('foo', $factory);
        $this->ruleFactory->createRule('foo');
    }

    public function testCreatingRuleWithNoFactoryRegisteredThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No factory registered for rule "foo"');
        $this->ruleFactory->createRule('foo');
    }

    public function testFactoryThatDoesNotTakeParametersReturnsRuleInstance(): void
    {
        $expectedRule = $this->createMock(IRule::class);
        $factory = function () use ($expectedRule) {
            return $expectedRule;
        };
        $this->ruleFactory->registerRuleFactory('foo', $factory);
        $this->assertSame($expectedRule, $this->ruleFactory->createRule('foo'));
    }

    public function testFactoryThatTakesParametersReturnsRuleInstance(): void
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
