<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\UriTemplates\Rules;

use InvalidArgumentException;
use Opulence\Routing\UriTemplates\Rules\IRule;
use Opulence\Routing\UriTemplates\Rules\RuleFactory;
use RuntimeException;

/**
 * Tests the rule factory
 */
class RuleFactoryTest extends \PHPUnit\Framework\TestCase
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
        $factory = function () {
            return [];
        };
        $this->ruleFactory->registerRuleFactory('foo', $factory);
        $this->ruleFactory->createRule('foo');
    }

    public function testCreatingRuleWithNoFactoryRegisteredThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
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
