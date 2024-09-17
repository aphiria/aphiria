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
use Aphiria\Authentication\AuthenticationScheme;
use Aphiria\Authentication\AuthenticationSchemeOptions;
use Aphiria\Authentication\AuthenticationSchemeRegistry;
use Aphiria\Authentication\IAuthenticationSchemeHandlerResolver;
use Aphiria\Authentication\MockAuthenticator;
use Aphiria\Authentication\Schemes\IAuthenticationSchemeHandler;
use Aphiria\Net\Http\IRequest;
use Aphiria\Security\Identity;
use Aphiria\Security\User;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class MockAuthenticatorTest extends TestCase
{
    private IAuthenticationSchemeHandlerResolver&MockInterface $authenticationHandlerResolver;
    private MockAuthenticator $mockAuthenticator;
    private AuthenticationSchemeRegistry $schemes;

    protected function setUp(): void
    {
        $this->schemes = new AuthenticationSchemeRegistry();
        $this->authenticationHandlerResolver = Mockery::mock(IAuthenticationSchemeHandlerResolver::class);
        $this->mockAuthenticator = new MockAuthenticator($this->schemes, $this->authenticationHandlerResolver);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testAuthenticatingWhileActingAsPrincipalAuthenticatesSuccessfullyAndDoesNotCallUnderlyingSchemeHandler(): void
    {
        $this->markTestSkipped('Waiting until https://github.com/mockery/mockery/issues/1438 is implemented');
        $request = Mockery::mock(IRequest::class);
        $user = new User([new Identity()]);
        [$scheme, $schemeHandler] = $this->createSchemeAndSetUpResolver('foo');
        $schemeHandler->shouldNotReceive('authenticate');
        $this->schemes->registerScheme($scheme, true);

        $result = $this->mockAuthenticator->actingAs($user, fn (): AuthenticationResult => $this->mockAuthenticator->authenticate($request, 'foo'));

        $this->assertTrue($result->passed);
        $this->assertSame($user, $result->user);
    }

    public function testAuthenticatingWhileActingAsPrincipalAuthenticatesSuccessfullyOnlyForTheScopedAuthenticationCall(): void
    {
        $this->markTestSkipped('Waiting until https://github.com/mockery/mockery/issues/1438 is implemented');
        $request = Mockery::mock(IRequest::class);
        $user = new User([new Identity()]);
        [$fooScheme, $fooSchemeHandler] = $this->createSchemeAndSetUpResolver('foo');
        $fooSchemeHandler->shouldReceive('authenticate')
            ->andReturn(AuthenticationResult::fail('foo', 'foo'));
        [$barScheme, $barSchemeHandler] = $this->createSchemeAndSetUpResolver('bar');
        [$bazScheme, $bazSchemeHandler] = $this->createSchemeAndSetUpResolver('baz');
        $bazSchemeHandler->shouldReceive('authenticate')
            ->andReturn(AuthenticationResult::fail('baz', 'baz'));
        $this->schemes->registerScheme($fooScheme, true);
        $this->schemes->registerScheme($barScheme);
        $this->schemes->registerScheme($bazScheme);

        $fooResult = $this->mockAuthenticator->authenticate($request, 'foo');
        $barResult = $this->mockAuthenticator->actingAs($user, fn (): AuthenticationResult => $this->mockAuthenticator->authenticate($request, 'bar'));
        $bazResult = $this->mockAuthenticator->authenticate($request, 'baz');

        $this->assertFalse($fooResult->passed);
        $this->assertTrue($barResult->passed);
        $this->assertSame($user, $barResult->user);
        $this->assertFalse($bazResult->passed);
    }

    public function testAuthenticatingWhileNotActingAsPrincipalAuthenticatesSuccessfullyForValidRequest(): void
    {
        $this->markTestSkipped('Waiting until https://github.com/mockery/mockery/issues/1438 is implemented');
        $request = Mockery::mock(IRequest::class);
        $user = new User([new Identity()]);
        [$scheme, $schemeHandler] = $this->createSchemeAndSetUpResolver('foo');
        $schemeHandler->shouldReceive('authenticate')
            ->andReturn(AuthenticationResult::pass($user, 'foo'));
        $this->schemes->registerScheme($scheme, true);

        $result = $this->mockAuthenticator->authenticate($request, 'foo');

        $this->assertTrue($result->passed);
        $this->assertSame($user, $result->user);
    }

    public function testAuthenticatingWhileNotActingAsPrincipalAuthenticatesUnsuccessfullyForInvalidRequest(): void
    {
        $this->markTestSkipped('Waiting until https://github.com/mockery/mockery/issues/1438 is implemented');
        $request = Mockery::mock(IRequest::class);
        [$scheme, $schemeHandler] = $this->createSchemeAndSetUpResolver('foo');
        $schemeHandler->shouldReceive('authenticate')
            ->andReturn(AuthenticationResult::fail('foo', 'foo'));
        $this->schemes->registerScheme($scheme, true);

        $result = $this->mockAuthenticator->authenticate($request, 'foo');

        $this->assertFalse($result->passed);
    }

    /**
     * A helper method to create an authentication scheme and set up the resolver to resolve its handler
     *
     * @param string $schemeName The name of the scheme to create
     * @return array{0: AuthenticationScheme, 1: IAuthenticationSchemeHandler<AuthenticationSchemeOptions>&MockInterface} The created authentication scheme and handler
     */
    private function createSchemeAndSetUpResolver(string $schemeName): array
    {
        // PHPUnit will not assign unique mock class names if you create multiple
        // So, we'll use a mock builder to ensure that the generated class names are unique
        $schemeHandlerClassName = "{$schemeName}_SchemeHandler";
        /** @var IAuthenticationSchemeHandler<AuthenticationSchemeOptions>&MockInterface $schemeHandler */
        $schemeHandler = Mockery::namedMock($schemeHandlerClassName, IAuthenticationSchemeHandler::class);
        $scheme = new AuthenticationScheme($schemeName, $schemeHandler::class);
        $this->authenticationHandlerResolver->shouldReceive('resolve')
            ->with($schemeHandlerClassName)
            ->andReturn($schemeHandler);
        $this->schemes->registerScheme($scheme);

        return [$scheme, $schemeHandler];
    }
}
