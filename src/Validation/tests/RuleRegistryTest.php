<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests;

use Aphiria\Validation\RuleRegistry;
use Aphiria\Validation\Rules\IRule;
use PHPUnit\Framework\TestCase;

/**
 * Tests the rule registry
 */
class RuleRegistryTest extends TestCase
{
    private RuleRegistry $rules;

    protected function setUp(): void
    {
        $this->rules = new RuleRegistry();
    }

    public function testGettingAllMethodRulesForClassWithNoRulesReturnsEmptyArray(): void
    {
        $this->assertCount(0, $this->rules->getAllMethodRules(\get_class($this)));
    }

    public function testGettingAllMethodRulesForClassWithRulesReturnsThem(): void
    {
        $rule1 = $this->createMock(IRule::class);
        $rule2 = $this->createMock(IRule::class);
        $this->rules->registerMethodRules(\get_class($this), 'foo', [$rule1, $rule2]);
        $this->assertEquals(
            ['foo' => [$rule1, $rule2]],
            $this->rules->getAllMethodRules(\get_class($this))
        );
    }

    public function testGettingAllPropertyRulesForClassWithNoRulesReturnsEmptyArray(): void
    {
        $this->assertCount(0, $this->rules->getAllPropertyRules(\get_class($this)));
    }

    public function testGettingAllPropertyRulesForClassWithRulesReturnsThem(): void
    {
        $rule1 = $this->createMock(IRule::class);
        $rule2 = $this->createMock(IRule::class);
        $this->rules->registerPropertyRules(\get_class($this), 'foo', [$rule1, $rule2]);
        $this->assertEquals(
            ['foo' => [$rule1, $rule2]],
            $this->rules->getAllPropertyRules(\get_class($this))
        );
    }

    public function testGettingMethodRulesForClassWithNoRulesReturnsEmptyArray(): void
    {
        $this->assertCount(0, $this->rules->getMethodRules(\get_class($this), 'foo'));
    }

    public function testGettingMethodRulesForClassWithRulesReturnsThem(): void
    {
        $rule = $this->createMock(IRule::class);
        $this->rules->registerMethodRules(\get_class($this), 'foo', $rule);
        $this->assertEquals([$rule], $this->rules->getMethodRules(\get_class($this), 'foo'));
    }

    public function testGettingPropertyRulesForClassWithNoRulesReturnsEmptyArray(): void
    {
        $this->assertCount(0, $this->rules->getPropertyRules(\get_class($this), 'foo'));
    }

    public function testGettingPropertyRulesForClassWithRulesReturnsThem(): void
    {
        $rule = $this->createMock(IRule::class);
        $this->rules->registerPropertyRules(\get_class($this), 'foo', $rule);
        $this->assertEquals([$rule], $this->rules->getPropertyRules(\get_class($this), 'foo'));
    }

    public function testRegisteringMultipleMethodRulesWorks(): void
    {
        $rule1 = $this->createMock(IRule::class);
        $rule2 = $this->createMock(IRule::class);
        $this->rules->registerMethodRules(\get_class($this), 'foo', [$rule1, $rule2]);
        $this->assertEquals(
            ['foo' => [$rule1, $rule2]],
            $this->rules->getAllMethodRules(\get_class($this))
        );
    }

    public function testRegisteringSingleMethodRuleWorks(): void
    {
        $rule = $this->createMock(IRule::class);
        $this->rules->registerMethodRules(\get_class($this), 'foo', $rule);
        $this->assertEquals([$rule], $this->rules->getMethodRules(\get_class($this), 'foo'));
    }

    public function testRegisteringMultiplePropertyRulesWorks(): void
    {
        $rule1 = $this->createMock(IRule::class);
        $rule2 = $this->createMock(IRule::class);
        $this->rules->registerPropertyRules(\get_class($this), 'foo', [$rule1, $rule2]);
        $this->assertEquals(
            ['foo' => [$rule1, $rule2]],
            $this->rules->getAllPropertyRules(\get_class($this))
        );
    }

    public function testRegisteringSinglePropertyRuleWorks(): void
    {
        $rule = $this->createMock(IRule::class);
        $this->rules->registerPropertyRules(\get_class($this), 'foo', $rule);
        $this->assertEquals([$rule], $this->rules->getPropertyRules(\get_class($this), 'foo'));
    }
}
