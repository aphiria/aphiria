<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Rules;

use Aphiria\Validation\ValidationContext;
use InvalidArgumentException;
use Aphiria\Validation\Rules\CallbackRule;
use Aphiria\Validation\Rules\IRule;
use Aphiria\Validation\Rules\RuleExtensionRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the rule extension registry
 */
class RuleExtensionRegistryTest extends TestCase
{
    private RuleExtensionRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new RuleExtensionRegistry();
    }

    public function testCallbackGetsConvertedToRule(): void
    {
        $context = new ValidationContext($this);
        $rule = function () {
            return true;
        };
        /** @var IRule|MockObject $rule */
        $this->registry->registerRuleExtension($rule, 'foo');
        $this->assertInstanceOf(CallbackRule::class, $this->registry->getRule('foo'));
        $this->assertTrue($this->registry->getRule('foo')->passes('bar', $context));
    }

    public function testCheckingIfRegistryHasRule(): void
    {
        /** @var IRule|MockObject $rule */
        $rule = $this->createMock(IRule::class);
        $rule->expects($this->once())
            ->method('getSlug')
            ->willReturn('foo');
        $this->registry->registerRuleExtension($rule);
        $this->assertTrue($this->registry->hasRule('foo'));
        $this->assertFalse($this->registry->hasRule('bar'));
    }

    public function testExceptionThrownWhenNoExtensionExists(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->registry->getRule('foo');
    }

    public function testExceptionThrownWhenRegisteringAnInvalidRule(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->registry->registerRuleExtension('foo', 'bar');
    }

    public function testGettingRuleObject(): void
    {
        /** @var IRule|MockObject $rule */
        $rule = $this->createMock(IRule::class);
        $rule->expects($this->once())
            ->method('getSlug')
            ->willReturn('foo');
        $this->registry->registerRuleExtension($rule);
        $this->assertSame($rule, $this->registry->getRule('foo'));
    }

    public function testSlugIgnoredIfRegisteringRuleObject(): void
    {
        /** @var IRule|MockObject $rule */
        $rule = $this->createMock(IRule::class);
        $rule->expects($this->once())
            ->method('getSlug')
            ->willReturn('foo');
        $this->registry->registerRuleExtension($rule, 'bar');
        $this->assertTrue($this->registry->hasRule('foo'));
    }
}
