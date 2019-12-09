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
use Aphiria\Validation\ValidationContext;
use Aphiria\Validation\Validator;
use PHPUnit\Framework\TestCase;

/**
 * Tests the validator
 */
class ValidatorTest extends TestCase
{
    private Validator $validator;
    private RuleRegistry $rules;

    protected function setUp(): void
    {
        $this->rules = new RuleRegistry();
        $this->validator = new Validator($this->rules);
    }

    public function testTryValidateMethodReturnsFalseForInvalidValue(): void
    {
        $object = new class() {
            public function method(): int
            {
                return 1;
            }
        };
        $expectedContext = new ValidationContext($object, null, 'method');
        $rules = [$this->createMockRule(false, 1, $expectedContext)];
        $this->rules->registerMethodRules(\get_class($object), 'method', $rules);
        $this->assertFalse($this->validator->tryValidateMethod($object, 'method', $expectedContext));
    }

    public function testTryValidateMethodReturnsTrueForValidValue(): void
    {
        $object = new class() {
            public function method(): int
            {
                return 1;
            }
        };
        $expectedContext = new ValidationContext($object, null, 'method');
        $rules = [$this->createMockRule(true, 1, $expectedContext)];
        $this->rules->registerMethodRules(\get_class($object), 'method', $rules);
        $this->assertTrue($this->validator->tryValidateMethod($object, 'method', $expectedContext));
    }

    public function testTryValidateMethodWithInvalidValuePopulatesRuleViolations(): void
    {
        $object = new class() {
            public function method(): int
            {
                return 1;
            }
        };
        $expectedContext = new ValidationContext($object, null, 'method');
        $rules = [$this->createMockRule(false, 1, $expectedContext)];
        $this->rules->registerMethodRules(\get_class($object), 'method', $rules);
        $this->assertFalse($this->validator->tryValidateMethod($object, 'method', $expectedContext));
        $this->assertCount(1, $expectedContext->getRuleViolations());
        $this->assertSame($rules[0], $expectedContext->getRuleViolations()[0]->getRule());
        $this->assertEquals($object, $expectedContext->getRuleViolations()[0]->getRootValue());
        $this->assertEquals(1, $expectedContext->getRuleViolations()[0]->getInvalidValue());
    }

    public function testTryValidateMethodWithValidValueHasNoRuleViolations(): void
    {
        $object = new class() {
            public function method(): int
            {
                return 1;
            }
        };
        $expectedContext = new ValidationContext($object, null, 'method');
        $rules = [$this->createMockRule(true, 1, $expectedContext)];
        $this->rules->registerMethodRules(\get_class($object), 'method', $rules);
        $this->assertTrue($this->validator->tryValidateMethod($object, 'method', $expectedContext));
        $this->assertCount(0, $expectedContext->getRuleViolations());
    }

    public function testTryValidateObjectReturnsFalseForInvalidValue(): void
    {
        $object = new class() {
            public int $prop = 1;
        };
        $expectedObjectContext = new ValidationContext($object);
        $expectedPropContext = new ValidationContext($object, 'prop', null, $expectedObjectContext);
        $rules = [$this->createMockRule(false, 1, $expectedPropContext)];
        $this->rules->registerPropertyRules(\get_class($object), 'prop', $rules);
        $this->assertFalse($this->validator->tryValidateObject($object, $expectedObjectContext));
    }

    public function testTryValidateObjectReturnsTrueForValidValue(): void
    {
        $object = new class() {
            public int $prop = 1;
        };
        $expectedObjectContext = new ValidationContext($object);
        $expectedPropContext = new ValidationContext($object, 'prop', null, $expectedObjectContext);
        $rules = [$this->createMockRule(true, 1, $expectedPropContext)];
        $this->rules->registerPropertyRules(\get_class($object), 'prop', $rules);
        $this->assertTrue($this->validator->tryValidateObject($object, $expectedObjectContext));
    }

    public function testTryValidatePropertyReturnsFalseForInvalidValue(): void
    {
        $object = new class() {
            public int $prop = 1;
        };
        $expectedContext = new ValidationContext($object, 'prop');
        $rules = [$this->createMockRule(false, 1, $expectedContext)];
        $this->rules->registerPropertyRules(\get_class($object), 'prop', $rules);
        $this->assertFalse($this->validator->tryValidateProperty($object, 'prop', $expectedContext));
    }

    public function testTryValidatePropertyReturnsTrueForValidValue(): void
    {
        $object = new class() {
            public int $prop = 1;
        };
        $expectedContext = new ValidationContext($object, 'prop');
        $rules = [$this->createMockRule(true, 1, $expectedContext)];
        $this->rules->registerPropertyRules(\get_class($object), 'prop', $rules);
        $this->assertTrue($this->validator->tryValidateProperty($object, 'prop', $expectedContext));
    }

    public function testTryValidateValueReturnsFalseForInvalidValue(): void
    {
        $expectedContext = new ValidationContext('foo');
        $rules = [$this->createMockRule(false, 'foo', $expectedContext)];
        $this->assertFalse($this->validator->tryValidateValue('foo', $rules, $expectedContext));
    }

    public function testTryValidateValueReturnsTrueForValidValue(): void
    {
        $expectedContext = new ValidationContext('foo');
        $rules = [$this->createMockRule(true, 'foo', $expectedContext)];
        $this->assertTrue($this->validator->tryValidateValue('foo', $rules, $expectedContext));
    }

    public function testTryValidateValueWithInvalidValueSetsRuleViolations(): void
    {
        $expectedContext = new ValidationContext('foo');
        $rules = [$this->createMockRule(false, 'foo', $expectedContext)];
        $this->assertFalse($this->validator->tryValidateValue('foo', $rules, $expectedContext));
        $this->assertCount(1, $expectedContext->getRuleViolations());
        $this->assertSame($rules[0], $expectedContext->getRuleViolations()[0]->getRule());
        $this->assertEquals('foo', $expectedContext->getRuleViolations()[0]->getRootValue());
        $this->assertEquals('foo', $expectedContext->getRuleViolations()[0]->getInvalidValue());
    }

    public function testTryValidateValueWithValidValueHasNoRuleViolations(): void
    {
        $expectedContext = new ValidationContext('foo');
        $rules = [$this->createMockRule(true, 'foo', $expectedContext)];
        $this->assertTrue($this->validator->tryValidateValue('foo', $rules, $expectedContext));
        $this->assertCount(0, $expectedContext->getRuleViolations());
    }

    /**
     * Creates a mock rule
     *
     * @param bool $shouldPass Whether or not the rule should pass
     * @param mixed $value The value that will be passed
     * @param ValidationContext $expectedContext The validation context that will be passed
     * @return IRule The created rule
     */
    private function createMockRule(bool $shouldPass, $value, ValidationContext $expectedContext): IRule
    {
        $rule = $this->createMock(IRule::class);
        $rule->expects($this->once())
            ->method('passes')
            ->with($value, $expectedContext)
            ->willReturn($shouldPass);

        return $rule;
    }
}
