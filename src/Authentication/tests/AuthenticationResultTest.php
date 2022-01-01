<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication\Tests;

use Aphiria\Authentication\AuthenticationResult;
use Aphiria\Security\IPrincipal;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class AuthenticationResultTest extends TestCase
{
    public function testFailConvertsStringToException(): void
    {
        $result = AuthenticationResult::fail('foo');
        $this->assertInstanceOf(Exception::class, $result->failure);
        $this->assertSame('foo', $result->failure?->getMessage());
    }

    public function testFailingWithoutFailureSetThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed authentication results must specify a failure reason');
        new AuthenticationResult(false);
    }

    public function testFailSetsFailureToException(): void
    {
        $expectedException = new RuntimeException('foo');
        $result = AuthenticationResult::fail($expectedException);
        $this->assertSame($expectedException, $result->failure);
    }

    public function testFailSetsPassedToFalse(): void
    {
        $result = AuthenticationResult::fail('foo');
        $this->assertFalse($result->passed);
    }

    public function testPassingWithoutUserSetThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Passing authentication results must specify a user');
        new AuthenticationResult(true);
    }

    public function testPassSetsPassedToTrue(): void
    {
        $result = AuthenticationResult::pass($this->createMock(IPrincipal::class));
        $this->assertTrue($result->passed);
    }
}
