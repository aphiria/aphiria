<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Authentication\Components;

use Aphiria\Authentication\AuthenticationScheme;
use Aphiria\Authentication\AuthenticationSchemeOptions;
use Aphiria\Authentication\AuthenticationSchemeRegistry;
use Aphiria\Authentication\Schemes\IAuthenticationSchemeHandler;
use Aphiria\DependencyInjection\Container;
use Aphiria\Framework\Authentication\Components\AuthenticationComponent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthenticationComponentTest extends TestCase
{
    private AuthenticationComponent $authenticationComponent;
    private Container $container;
    private AuthenticationSchemeRegistry $schemes;

    protected function setUp(): void
    {
        // Using a real container to simplify testing
        $this->container = new Container();
        $this->authenticationComponent = new AuthenticationComponent($this->container);

        $this->container->bindInstance(AuthenticationSchemeRegistry::class, $this->schemes = new AuthenticationSchemeRegistry());
    }

    public function testBuildRegistersSchemes(): void
    {
        /** @var IAuthenticationSchemeHandler<AuthenticationSchemeOptions>&MockObject $schemeHandler */
        $schemeHandler = $this->createMock(IAuthenticationSchemeHandler::class);
        $defaultScheme = new AuthenticationScheme('foo', $schemeHandler::class);
        $nonDefaultScheme = new AuthenticationScheme('bar', $schemeHandler::class);
        $this->authenticationComponent->withScheme($defaultScheme, true);
        $this->authenticationComponent->withScheme($nonDefaultScheme, false);
        $this->authenticationComponent->build();
        $this->assertSame($defaultScheme, $this->schemes->getDefaultScheme());
        $this->assertSame($defaultScheme, $this->schemes->getScheme('foo'));
        $this->assertSame($nonDefaultScheme, $this->schemes->getScheme('bar'));
    }
}
