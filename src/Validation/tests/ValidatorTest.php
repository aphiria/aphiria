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

use Aphiria\Validation\CircularDependencyException;
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

    public function testTryValidateMethodWillRecursivelyValidateObjects(): void
    {
        $innerObject = new class() {
            public int $prop = 1;
        };
        $outerObject = new class($innerObject) {
            private object $innerObject;

            public function __construct(object $innerObject)
            {
                $this->innerObject = $innerObject;
            }

            public function method(): object
            {
                return $this->innerObject;
            }
        };
        $expectedOuterMethodContext = new ValidationContext($outerObject, null, 'method');
        $expectedInnerObjectContext = new ValidationContext($innerObject, null, null, $expectedOuterMethodContext);
        $expectedInnerObjectPropContext = new ValidationContext($innerObject, 'prop', null, $expectedInnerObjectContext);
        $this->rules->registerPropertyRules(
            \get_class($innerObject),
            'prop',
            $this->createMockRule(true, 1, $expectedInnerObjectPropContext)
        );
        $this->assertTrue($this->validator->tryValidateMethod($outerObject, 'method', $expectedOuterMethodContext));
    }

    public function testTryValidateMethodWithMagicMethodIsSkipped(): void
    {
        $object = new class {
            public function __toString(): string
            {
                die('Should not get here');
            }
        };
        $context = new ValidationContext($object, null, '__toString');
        $this->assertTrue($this->validator->tryValidateMethod($object, '__toString', $context));
    }

    public function testTryValidateMethodWithRequiredParamsIsSkipped(): void
    {
        $object = new class {
            public function foo(int $foo): string
            {
                die('Should not get here');
            }
        };
        $context = new ValidationContext($object, null, 'foo');
        $this->assertTrue($this->validator->tryValidateMethod($object, 'foo', $context));
    }

    public function testTryValidateMethodWithAPassedRuleAndAFailedRuleReturnsFalse(): void
    {
        $object = new class() {
            public function method(): int
            {
                return 1;
            }
        };
        $expectedContext = new ValidationContext($object, null, 'method');
        $rules = [
            $this->createMockRule(true, 1, $expectedContext),
            $this->createMockRule(false, 1, $expectedContext)
        ];
        $this->rules->registerMethodRules(\get_class($object), 'method', $rules);
        $this->assertFalse($this->validator->tryValidateMethod($object, 'method', $expectedContext));
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

    public function testTryValidateMethodWithInvalidValueSetsRuleViolations(): void
    {
        $object = new class {
            public function method(): int
            {
                return 1;
            }
        };
        $expectedMethodContext = new ValidationContext($object, 'method');
        $rules = [$this->createMockRule(false, 1, $expectedMethodContext)];
        $this->rules->registerMethodRules(\get_class($object), 'method', $rules);
        $this->assertFalse($this->validator->tryValidateMethod($object, 'method', $expectedMethodContext));
        $this->assertCount(1, $expectedMethodContext->getRuleViolations());
        $this->assertSame($rules[0], $expectedMethodContext->getRuleViolations()[0]->getRule());
        $this->assertEquals($object, $expectedMethodContext->getRuleViolations()[0]->getRootValue());
        $this->assertEquals(1, $expectedMethodContext->getRuleViolations()[0]->getInvalidValue());
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

    public function testTryValidateObjectWillRecursivelyValidateObjects(): void
    {
        $innerObject = new class() {
            public int $prop = 1;
        };
        $outerObject = new class($innerObject) {
            public object $innerObject;

            public function __construct(object $innerObject)
            {
                $this->innerObject = $innerObject;
            }
        };
        $expectedOuterContext = new ValidationContext($outerObject);
        $expectedOuterObjectPropContext = new ValidationContext($outerObject, 'innerObject', null, $expectedOuterContext);
        $expectedInnerObjectContext = new ValidationContext($innerObject, null, null, $expectedOuterObjectPropContext);
        $expectedInnerObjectPropContext = new ValidationContext($innerObject, 'prop', null, $expectedInnerObjectContext);
        $this->rules->registerPropertyRules(
            \get_class($innerObject),
            'prop',
            $this->createMockRule(true, 1, $expectedInnerObjectPropContext)
        );
        $this->assertTrue($this->validator->tryValidateObject($outerObject, $expectedOuterContext));
    }

    public function testTryValidateObjectWithAPassedRuleAndAFailedRuleReturnsFalse(): void
    {
        $object = new class() {
            public int $prop = 1;
        };
        $expectedObjectContext = new ValidationContext($object);
        $expectedPropContext = new ValidationContext($object, 'prop', null, $expectedObjectContext);
        $rules = [
            $this->createMockRule(true, 1, $expectedPropContext),
            $this->createMockRule(false, 1, $expectedPropContext)
        ];
        $this->rules->registerPropertyRules(\get_class($object), 'prop', $rules);
        $this->assertFalse($this->validator->tryValidateObject($object, $expectedObjectContext));
    }

    public function testTryValidateObjectWithInvalidValueSetsRuleViolations(): void
    {
        $object = new class {
            public int $prop = 1;
        };
        $expectedObjectContext = new ValidationContext($object);
        $expectedPropContext = new ValidationContext($object, 'prop', null, $expectedObjectContext);
        $rules = [$this->createMockRule(false, 1, $expectedPropContext)];
        $this->rules->registerPropertyRules(\get_class($object), 'prop', $rules);
        $this->assertFalse($this->validator->tryValidateObject($object, $expectedObjectContext));
        $this->assertCount(1, $expectedObjectContext->getRuleViolations());
        $this->assertSame($rules[0], $expectedObjectContext->getRuleViolations()[0]->getRule());
        $this->assertEquals($object, $expectedObjectContext->getRuleViolations()[0]->getRootValue());
        $this->assertEquals(1, $expectedObjectContext->getRuleViolations()[0]->getInvalidValue());
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

    public function testTryValidatePropertyWillRecursivelyValidateObjects(): void
    {
        $innerObject = new class() {
            public int $prop = 1;
        };
        $outerObject = new class($innerObject) {
            public object $innerObject;

            public function __construct(object $innerObject)
            {
                $this->innerObject = $innerObject;
            }
        };
        $expectedOuterPropContext = new ValidationContext($outerObject, 'innerObject');
        $expectedInnerObjectContext = new ValidationContext($innerObject, null, null, $expectedOuterPropContext);
        $expectedInnerObjectPropContext = new ValidationContext($innerObject, 'prop', null, $expectedInnerObjectContext);
        $this->rules->registerPropertyRules(
            \get_class($innerObject),
            'prop',
            $this->createMockRule(true, 1, $expectedInnerObjectPropContext)
        );
        $this->assertTrue($this->validator->tryValidateProperty($outerObject, 'innerObject', $expectedOuterPropContext));
    }

    public function testTryValidatePropertyWithAPassedRuleAndAFailedRuleReturnsFalse(): void
    {
        $object = new class() {
            public int $prop = 1;
        };
        $expectedPropContext = new ValidationContext($object, 'prop');
        $rules = [
            $this->createMockRule(true, 1, $expectedPropContext),
            $this->createMockRule(false, 1, $expectedPropContext)
        ];
        $this->rules->registerPropertyRules(\get_class($object), 'prop', $rules);
        $this->assertFalse($this->validator->tryValidateProperty($object, 'prop', $expectedPropContext));
    }

    public function testTryValidatePropertyWithInvalidValueSetsRuleViolations(): void
    {
        $object = new class {
            public int $prop = 1;
        };
        $expectedPropContext = new ValidationContext($object, 'prop');
        $rules = [$this->createMockRule(false, 1, $expectedPropContext)];
        $this->rules->registerPropertyRules(\get_class($object), 'prop', $rules);
        $this->assertFalse($this->validator->tryValidateProperty($object, 'prop', $expectedPropContext));
        $this->assertCount(1, $expectedPropContext->getRuleViolations());
        $this->assertSame($rules[0], $expectedPropContext->getRuleViolations()[0]->getRule());
        $this->assertEquals($object, $expectedPropContext->getRuleViolations()[0]->getRootValue());
        $this->assertEquals(1, $expectedPropContext->getRuleViolations()[0]->getInvalidValue());
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

    public function testValidateMethodWithCircularDependencyThrowsException(): void
    {
        $object1 = new class {
            public ?object $methodReturnValue = null;

            public function method(): object
            {
                return $this->methodReturnValue;
            }
        };
        $object2 = new class {
            public ?object $methodReturnValue = null;

            public function method(): object
            {
                return $this->methodReturnValue;
            }
        };
        $object1->methodReturnValue = $object2;
        $object2->methodReturnValue = $object1;
        $this->expectException(CircularDependencyException::class);
        $this->expectExceptionMessage('Circular dependency on ' . \get_class($object2) . ' detected');
        $expectedMethodContext = new ValidationContext($object1, null, 'method');
        $this->validator->validateMethod($object1, 'method', $expectedMethodContext);
    }

    public function testValidateObjectWithCircularDependencyThrowsException(): void
    {
        $object1 = new class {
            public ?object $prop = null;
        };
        $object2 = new class {
            public ?object $prop = null;
        };
        $object1->prop = $object2;
        $object2->prop = $object1;
        $this->expectException(CircularDependencyException::class);
        // Due to the order that objects are recursively validated, object1 will show up as the circular dependency
        $this->expectExceptionMessage('Circular dependency on ' . \get_class($object1) . ' detected');
        $expectedObjectContext = new ValidationContext($object1);
        $this->validator->validateObject($object1, $expectedObjectContext);
    }

    public function testValidatePropertyWithCircularDependencyThrowsException(): void
    {
        $object1 = new class {
            public ?object $prop = null;
        };
        $object2 = new class {
            public ?object $prop = null;
        };
        $object1->prop = $object2;
        $object2->prop = $object1;
        $this->expectException(CircularDependencyException::class);
        $this->expectExceptionMessage('Circular dependency on ' . \get_class($object2) . ' detected');
        $expectedPropertyContext = new ValidationContext($object1, 'prop');
        $this->validator->validateProperty($object1, 'prop', $expectedPropertyContext);
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
