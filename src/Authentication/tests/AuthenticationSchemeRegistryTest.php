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

use Aphiria\Authentication\AuthenticationScheme;
use Aphiria\Authentication\AuthenticationSchemeOptions;
use Aphiria\Authentication\AuthenticationSchemeRegistry;
use Aphiria\Authentication\Schemes\IAuthenticationSchemeHandler;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

class AuthenticationSchemeRegistryTest extends TestCase
{
    private AuthenticationSchemeRegistry $schemes;

    protected function setUp(): void
    {
        $this->schemes = new AuthenticationSchemeRegistry();
    }

    public function testGetDefaultSchemeReturnsNullIfNoDefaultSchemeIsRegistered(): void
    {
        $this->assertNull($this->schemes->defaultScheme);
    }

    public function testGetDefaultSchemeReturnsSchemeIfItIsTheOnlyOneRegistered(): void
    {
        $this->assertNull($this->schemes->defaultScheme);
        /** @var IAuthenticationSchemeHandler<AuthenticationSchemeOptions> $schemeHandler1 */
        $schemeHandler1 = $this->createMock(IAuthenticationSchemeHandler::class);
        $scheme1 = new AuthenticationScheme('foo', $schemeHandler1::class, new AuthenticationSchemeOptions());
        $this->schemes->registerScheme($scheme1);
        $this->assertSame($scheme1, $this->schemes->defaultScheme);
        /** @var IAuthenticationSchemeHandler<AuthenticationSchemeOptions> $schemeHandler2 */
        $schemeHandler2 = $this->createMock(IAuthenticationSchemeHandler::class);
        $scheme2 = new AuthenticationScheme('bar', $schemeHandler2::class, new AuthenticationSchemeOptions());
        $this->schemes->registerScheme($scheme2);
        $this->assertNull($this->schemes->defaultScheme);
    }

    public function testGetSchemeWithMatchingSchemeReturnsIt(): void
    {
        /** @var IAuthenticationSchemeHandler<AuthenticationSchemeOptions> $schemeHandler */
        $schemeHandler = $this->createMock(IAuthenticationSchemeHandler::class);
        $expectedScheme = new AuthenticationScheme('foo', $schemeHandler::class, new AuthenticationSchemeOptions());
        $this->schemes->registerScheme($expectedScheme);
        $this->assertSame($expectedScheme, $this->schemes->getScheme('foo'));
    }

    public function testGetSchemeWithNoMatchingSchemeThrowsException(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('No authentication scheme with name "foo" found');
        $this->schemes->getScheme('foo');
    }

    public function testSettingSchemeAsDefaultMakesItDefault(): void
    {
        /** @var IAuthenticationSchemeHandler<AuthenticationSchemeOptions> $schemeHandler */
        $schemeHandler = $this->createMock(IAuthenticationSchemeHandler::class);
        $expectedScheme = new AuthenticationScheme('foo', $schemeHandler::class, new AuthenticationSchemeOptions());
        $this->schemes->registerScheme($expectedScheme, true);
        $this->assertSame($expectedScheme, $this->schemes->defaultScheme);

        // Test registering another non-default scheme to make sure it's not just marking the first one as default
        $nonDefaultScheme = new AuthenticationScheme('bar', $schemeHandler::class, new AuthenticationSchemeOptions());
        $this->schemes->registerScheme($nonDefaultScheme);
        $this->assertSame($expectedScheme, $this->schemes->defaultScheme);
    }
}
