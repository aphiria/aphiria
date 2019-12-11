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

use Aphiria\Validation\Rules\IRule;
use Aphiria\Validation\RuleViolation;
use PHPUnit\Framework\TestCase;

/**
 * Tests the rule violation
 */
class RuleViolationTest extends TestCase
{
    public function testGetInvalidValueReturnsOneSetInConstructor(): void
    {
        $violation = new RuleViolation(
            $this->createMock(IRule::class),
            'foo',
            'bar'
        );
        $this->assertEquals('foo', $violation->getInvalidValue());
    }

    public function testGetMethodNameReturnsOneSetInConstructor(): void
    {
        $violation = new RuleViolation(
            $this->createMock(IRule::class),
            'foo',
            'bar',
            null,
            'method'
        );
        $this->assertEquals('method', $violation->getMethodName());
    }

    public function testGetPropertyNameReturnsOneSetInConstructor(): void
    {
        $violation = new RuleViolation(
            $this->createMock(IRule::class),
            'foo',
            'bar',
            'prop'
        );
        $this->assertEquals('prop', $violation->getPropertyName());
    }

    public function testGetRootValueReturnsOneSetInConstructor(): void
    {
        $violation = new RuleViolation(
            $this->createMock(IRule::class),
            'foo',
            'bar'
        );
        $this->assertEquals('bar', $violation->getRootValue());
    }

    public function testGetRuleReturnsOneSetInConstructor(): void
    {
        $expectedRule = $this->createMock(IRule::class);
        $violation = new RuleViolation(
            $expectedRule,
            'foo',
            'bar'
        );
        $this->assertSame($expectedRule, $violation->getRule());
    }
}
