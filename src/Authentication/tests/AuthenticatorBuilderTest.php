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

use Aphiria\Authentication\AuthenticationScheme;
use Aphiria\Authentication\AuthenticationSchemeOptions;
use Aphiria\Authentication\AuthenticationSchemeRegistry;
use Aphiria\Authentication\Authenticator;
use Aphiria\Authentication\AuthenticatorBuilder;
use Aphiria\Authentication\IAuthenticationSchemeHandlerResolver;
use Aphiria\Authentication\IUserAccessor;
use Aphiria\Authentication\RequestPropertyUserAccessor;
use Aphiria\Authentication\Schemes\IAuthenticationSchemeHandler;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class AuthenticatorBuilderTest extends TestCase
{
    private AuthenticatorBuilder $authenticatorBuilder;

    protected function setUp(): void
    {
        $this->authenticatorBuilder = new AuthenticatorBuilder();
    }

    public function testBuildWithoutHandlerResolverThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No handler resolver was specified');
        $this->authenticatorBuilder->build();
    }

    public function testBuildWithoutUserAccessorUsesDefaultOne(): void
    {
        $schemeHandlerResolver = $this->createMock(IAuthenticationSchemeHandlerResolver::class);
        $authenticator = $this->authenticatorBuilder->withHandlerResolver($schemeHandlerResolver)
            ->build();
        $expectedAuthenticator = new Authenticator(new AuthenticationSchemeRegistry(), $schemeHandlerResolver, new RequestPropertyUserAccessor());
        $this->assertEquals($expectedAuthenticator, $authenticator);
    }

    public function testWithMethodsReturnSameInstance(): void
    {
        $instance1 = $this->authenticatorBuilder->withHandlerResolver($this->createMock(IAuthenticationSchemeHandlerResolver::class));
        /** @var IAuthenticationSchemeHandler<AuthenticationSchemeOptions> $schemeHandler */
        $schemeHandler = $this->createMock(IAuthenticationSchemeHandler::class);
        /** @psalm-suppress InvalidCast https://github.com/vimeo/psalm/issues/8810 - bug */
        $instance2 = $this->authenticatorBuilder->withScheme(new AuthenticationScheme('foo', $schemeHandler::class));
        $instance3 = $this->authenticatorBuilder->withUserAccessor($this->createMock(IUserAccessor::class));
        $this->assertTrue($instance1 === $instance2 && $instance2 === $instance3);
    }

    public function testWithSchemeCanMarkSchemeAsDefault(): void
    {
        $schemeHandlerResolver = $this->createMock(IAuthenticationSchemeHandlerResolver::class);
        /** @var IAuthenticationSchemeHandler<AuthenticationSchemeOptions> $schemeHandler */
        $schemeHandler = $this->createMock(IAuthenticationSchemeHandler::class);
        /** @psalm-suppress InvalidCast https://github.com/vimeo/psalm/issues/8810 - bug */
        $scheme = new AuthenticationScheme('foo', $schemeHandler::class);
        $authenticator = $this->authenticatorBuilder->withHandlerResolver($schemeHandlerResolver)
            ->withScheme($scheme, true)
            ->build();
        $expectedSchemes = new AuthenticationSchemeRegistry();
        $expectedSchemes->registerScheme($scheme, true);
        $expectedAuthenticator = new Authenticator($expectedSchemes, $schemeHandlerResolver);
        $this->assertEquals($expectedAuthenticator, $authenticator);
    }

    public function testWithSchemeAddsSchemeToAuthenticator(): void
    {
        $schemeHandlerResolver = $this->createMock(IAuthenticationSchemeHandlerResolver::class);
        /** @var IAuthenticationSchemeHandler<AuthenticationSchemeOptions> $schemeHandler */
        $schemeHandler = $this->createMock(IAuthenticationSchemeHandler::class);
        /** @psalm-suppress InvalidCast https://github.com/vimeo/psalm/issues/8810 - bug */
        $scheme = new AuthenticationScheme('foo', $schemeHandler::class);
        $authenticator = $this->authenticatorBuilder->withHandlerResolver($schemeHandlerResolver)
            ->withScheme($scheme)
            ->build();
        $expectedSchemes = new AuthenticationSchemeRegistry();
        $expectedSchemes->registerScheme($scheme);
        $expectedAuthenticator = new Authenticator($expectedSchemes, $schemeHandlerResolver);
        $this->assertEquals($expectedAuthenticator, $authenticator);
    }

    public function testWithUserAccessorSetsUserAccessor(): void
    {
        $schemeHandlerResolver = $this->createMock(IAuthenticationSchemeHandlerResolver::class);
        $userAccessor = $this->createMock(IUserAccessor::class);
        $authenticator = $this->authenticatorBuilder->withHandlerResolver($schemeHandlerResolver)
            ->withUserAccessor($userAccessor)
            ->build();
        $expectedAuthenticator = new Authenticator(new AuthenticationSchemeRegistry(), $schemeHandlerResolver, $userAccessor);
        $this->assertEquals($expectedAuthenticator, $authenticator);
    }
}
