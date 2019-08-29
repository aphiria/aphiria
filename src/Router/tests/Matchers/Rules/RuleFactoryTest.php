<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Matchers\Rules;

use Aphiria\Routing\Matchers\Rules\IRule;
use Aphiria\Routing\Matchers\Rules\RuleFactory;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tests the rule factory
 */
class RuleFactoryTest extends TestCase
{
    private RuleFactory $ruleFactory;

    protected function setUp(): void
    {
        $this->ruleFactory = new RuleFactory();
    }

    public function testClosureThatDoesNotReturnRuleInstanceThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Factory for rule "foo" does not return an instance of IRule');
        $factory = fn () => [];
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
        $factory = fn () => $expectedRule;
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
