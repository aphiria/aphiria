<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication\Tests;

use Aphiria\Authentication\AuthenticationResult;
use Aphiria\Authentication\Tests\Mocks\MockAuthenticationResult;
use Aphiria\Security\IPrincipal;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\TestWith;
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

    /**
     * @param list<string>|string $schemeNames The scheme names to test
     */
    #[TestWith(['foo', ['foo', 'bar']])]
    public function testFailSetsSchemeNames(array|string $schemeNames): void
    {
        $result = AuthenticationResult::fail(new RuntimeException('foo'), $schemeNames);
        $this->assertSame((array)$schemeNames, $result->schemeNames);
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

    /**
     * @param list<string>|string $schemeNames The scheme names to test
     */
    #[TestWith(['foo', ['foo', 'bar']])]
    public function testPassSetsSchemeNames(string|array $schemeNames): void
    {
        $result = AuthenticationResult::pass($this->createMock(IPrincipal::class), $schemeNames);
        $this->assertSame((array)$schemeNames, $result->schemeNames);
    }
}
