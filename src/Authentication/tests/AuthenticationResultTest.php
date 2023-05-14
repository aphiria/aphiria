<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication\Tests;

use Aphiria\Authentication\AuthenticationResult;
use Aphiria\Authentication\Tests\Mocks\MockAuthenticationResult;
use Aphiria\Security\IPrincipal;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class AuthenticationResultTest extends TestCase
{
    public function testFailConvertsStringToException(): void
    {
        $result = AuthenticationResult::fail('foo', 'bar');
        $this->assertInstanceOf(Exception::class, $result->failure);
        $this->assertSame('foo', $result->failure->getMessage());
    }

    public function testFailingWithoutFailureSetThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed authentication results must specify a failure reason');
        new MockAuthenticationResult(false, 'foo');
    }

    public function testFailSetsFailureToException(): void
    {
        $expectedException = new RuntimeException('foo');
        $result = AuthenticationResult::fail($expectedException, 'foo');
        $this->assertSame($expectedException, $result->failure);
    }

    public function testFailSetsPassedToFalse(): void
    {
        $result = AuthenticationResult::fail('foo', 'bar');
        $this->assertFalse($result->passed);
    }

    public function testFailSetsSchemeName(): void
    {
        $result = AuthenticationResult::fail(new RuntimeException('foo'), 'foo');
        $this->assertSame('foo', $result->schemeName);
    }

    public function testPassingWithoutUserSetThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Passing authentication results must specify a user');
        new MockAuthenticationResult(true, 'foo');
    }

    public function testPassSetsPassedToTrue(): void
    {
        $result = AuthenticationResult::pass($this->createMock(IPrincipal::class), 'foo');
        $this->assertTrue($result->passed);
    }

    public function testPassSetsSchemeName(): void
    {
        $result = AuthenticationResult::pass($this->createMock(IPrincipal::class), 'foo');
        $this->assertSame('foo', $result->schemeName);
    }
}
