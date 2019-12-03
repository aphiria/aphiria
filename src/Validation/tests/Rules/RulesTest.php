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
use BadMethodCallException;
use Countable;
use DateTime;
use Aphiria\Validation\Rules\Errors\Compilers\ICompiler;
use Aphiria\Validation\Rules\Errors\ErrorTemplateRegistry;
use Aphiria\Validation\Rules\IRule;
use Aphiria\Validation\Rules\IRuleWithArgs;
use Aphiria\Validation\Rules\RuleExtensionRegistry;
use Aphiria\Validation\Rules\Rules;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the rules
 */
class RulesTest extends TestCase
{
    private Rules $rules;
    /** @var RuleExtensionRegistry|MockObject The rule extension registry */
    private RuleExtensionRegistry $ruleExtensionRegistry;
    /** @var ErrorTemplateRegistry|MockObject The error template registry */
    private ErrorTemplateRegistry $errorTemplateRegistry;
    /** @var ICompiler|MockObject The error template compiler */
    private ICompiler $errorTemplateCompiler;

    protected function setUp(): void
    {
        $this->ruleExtensionRegistry = $this->createMock(RuleExtensionRegistry::class);
        $this->errorTemplateRegistry = $this->createMock(ErrorTemplateRegistry::class);
        $this->errorTemplateCompiler = $this->createMock(ICompiler::class);
        $this->rules = new Rules(
            $this->ruleExtensionRegistry,
            $this->errorTemplateRegistry,
            $this->errorTemplateCompiler
        );
    }

    public function testAlphaNumericRule(): void
    {
        $context = new ValidationContext($this);
        $this->assertSame($this->rules, $this->rules->alphaNumeric());
        $this->assertTrue($this->rules->pass('a1', $context));
        $this->assertFalse($this->rules->pass('a 1', $context));
    }

    public function testAlphaRule(): void
    {
        $context = new ValidationContext($this);
        $this->assertSame($this->rules, $this->rules->alpha());
        $this->assertTrue($this->rules->pass('a', $context));
        $this->assertFalse($this->rules->pass('a1', $context));
    }

    public function testBetweenRule(): void
    {
        $context = new ValidationContext($this);
        $this->assertSame($this->rules, $this->rules->between(1, 2, false));
        $this->assertFalse($this->rules->pass(1, $context));
        $this->assertFalse($this->rules->pass(2, $context));
        $this->assertTrue($this->rules->pass(1.5, $context));
    }

    public function testCallingExtension(): void
    {
        $context = new ValidationContext($this);
        $this->ruleExtensionRegistry->expects($this->once())
            ->method('hasRule')
            ->with('foo')
            ->willReturn(true);
        $rule = $this->createMock(IRule::class);
        $this->ruleExtensionRegistry->expects($this->once())
            ->method('getRule')
            ->willReturn($rule);
        $rule->expects($this->once())
            ->method('passes')
            ->with('bar')
            ->willReturn(true);
        $this->assertSame($this->rules, $this->rules->foo());
        $this->assertTrue($this->rules->pass('bar', $context));
    }

    public function testCallingExtensionWithArgs(): void
    {
        $context = new ValidationContext($this);
        $this->ruleExtensionRegistry->expects($this->once())
            ->method('hasRule')
            ->with('foo')
            ->willReturn(true);
        $rule = $this->createMock(IRuleWithArgs::class);
        $rule->expects($this->once())
            ->method('setArgs')
            ->with(['baz']);
        $this->ruleExtensionRegistry->expects($this->once())
            ->method('getRule')
            ->willReturn($rule);
        $rule->expects($this->once())
            ->method('passes')
            ->with('bar')
            ->willReturn(true);
        $this->assertSame($this->rules, $this->rules->foo('baz'));
        $this->assertTrue($this->rules->pass('bar', $context));
    }

    public function testCallingNonExistentExtension(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->ruleExtensionRegistry->expects($this->once())
            ->method('hasRule')
            ->with('foo')
            ->willReturn(false);
        $this->rules->foo('bar');
    }

    public function testCheckingRulesTwiceDoesNotAppendErrors(): void
    {
        $context = new ValidationContext($this);
        $this->errorTemplateRegistry->expects($this->exactly(2))
            ->method('getErrorTemplate')
            ->with('the-field', 'email')
            ->willReturn('');
        $this->errorTemplateCompiler->expects($this->exactly(2))
            ->method('compile')
            ->with('the-field', '', [])
            ->willReturn('The error');
        $this->rules->email();
        $this->rules->pass('foo', $context);
        $this->assertEquals(['The error'], $this->rules->getErrors('the-field'));
        $this->rules->pass('foo', $context);
        $this->assertEquals(['The error'], $this->rules->getErrors('the-field'));
    }

    public function testDateRule(): void
    {
        $context = new ValidationContext($this);
        $format1 = 'Y-m-d';
        $format2 = 'F j';
        $this->assertSame($this->rules, $this->rules->date([$format1, $format2]));
        $this->assertTrue($this->rules->pass((new DateTime)->format($format1), $context));
        $this->assertTrue($this->rules->pass((new DateTime)->format($format2), $context));
    }

    public function testEmailRule(): void
    {
        $context = new ValidationContext($this);
        $this->assertSame($this->rules, $this->rules->email());
        $this->assertTrue($this->rules->pass('foo@bar.com', $context));
    }

    public function testEqualsRule(): void
    {
        $context = new ValidationContext($this);
        $this->assertSame($this->rules, $this->rules->equals('bar'));
        $this->assertTrue($this->rules->pass('bar', $context));
    }

    public function testGettingErrorsWhenThereAreNone(): void
    {
        $context = new ValidationContext($this);
        $this->assertEquals([], $this->rules->getErrors('foo'));
        $this->rules->email();
        $this->rules->pass('foo@bar.com', $context);
        $this->assertEquals([], $this->rules->getErrors('foo'));
    }

    public function testIPAddressRule(): void
    {
        $context = new ValidationContext($this);
        $this->assertSame($this->rules, $this->rules->ipAddress());
        $this->assertTrue($this->rules->pass('127.0.0.1', $context));
    }

    public function testInRule(): void
    {
        $context = new ValidationContext($this);
        $this->assertSame($this->rules, $this->rules->in(['foo', 'bar']));
        $this->assertTrue($this->rules->pass('bar', $context));
    }

    public function testIntegerRule(): void
    {
        $context = new ValidationContext($this);
        $this->assertSame($this->rules, $this->rules->integer());
        $this->assertTrue($this->rules->pass(1, $context));
    }

    public function testMaxRule(): void
    {
        $context = new ValidationContext($this);
        $this->assertSame($this->rules, $this->rules->max(2, false));
        $this->assertFalse($this->rules->pass(2, $context));
        $this->assertTrue($this->rules->pass(1.9, $context));
    }

    public function testMinRule(): void
    {
        $context = new ValidationContext($this);
        $this->assertSame($this->rules, $this->rules->min(2, false));
        $this->assertFalse($this->rules->pass(2, $context));
        $this->assertTrue($this->rules->pass(2.1, $context));
    }

    public function testNonRequiredFieldPassesAllRulesWhenEmpty(): void
    {
        $context = new ValidationContext($this);
        $this->rules
            ->email()
            ->date('Y-m-d');
        $this->assertTrue($this->rules->pass(null, $context));
        $this->assertTrue($this->rules->pass([], $context));
        $countable = $this->createMock(Countable::class);
        $countable->expects($this->exactly(2))
            ->method('count')
            ->willReturn(0);
        $this->assertTrue($this->rules->pass($countable, $context));
    }

    public function testNotInRule(): void
    {
        $context = new ValidationContext($this);
        $this->assertSame($this->rules, $this->rules->notIn(['foo', 'bar']));
        $this->assertTrue($this->rules->pass('baz', $context));
    }

    public function testNumericRule(): void
    {
        $context = new ValidationContext($this);
        $this->assertSame($this->rules, $this->rules->numeric());
        $this->assertTrue($this->rules->pass(1.5, $context));
    }

    public function testRegexRule(): void
    {
        $context = new ValidationContext($this);
        $this->assertSame($this->rules, $this->rules->regex('/^[a-z]{3}$/'));
        $this->assertTrue($this->rules->pass('baz', $context));
    }

    public function testRequiredRule(): void
    {
        $context = new ValidationContext($this);
        $this->assertSame($this->rules, $this->rules->required());
        $this->assertTrue($this->rules->pass('bar', $context));
    }

    public function testsPassesWithNoRules(): void
    {
        $context = new ValidationContext($this);
        $this->assertTrue($this->rules->pass('bar', $context));
    }
}
