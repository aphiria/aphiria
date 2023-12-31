<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Authorization\Binders;

use Aphiria\Application\Configuration\GlobalConfiguration;
use Aphiria\Application\Configuration\HashTableConfiguration;
use Aphiria\Authentication\IUserAccessor;
use Aphiria\Authentication\RequestPropertyUserAccessor;
use Aphiria\Authorization\Authority;
use Aphiria\Authorization\AuthorizationPolicyRegistry;
use Aphiria\Authorization\AuthorizationRequirementHandlerRegistry;
use Aphiria\Authorization\IAuthority;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Authorization\Binders\AuthorizationBinder;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class AuthorizationBinderTest extends TestCase
{
    private AuthorizationBinder $binder;
    private IContainer&MockInterface $container;

    protected function setUp(): void
    {
        $this->container = Mockery::mock(IContainer::class);
        $this->binder = new AuthorizationBinder();
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testInstancesAreBoundToContainer(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->setUpContainerMock();
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    /**
     * Gets the base config
     *
     * @return array<string, mixed> The base config
     */
    private static function getBaseConfig(): array
    {
        return [
            'aphiria' => [
                'authorization' => [
                    'continueOnFailure' => true
                ]
            ]
        ];
    }

    /**
     * Sets up the container mock
     */
    private function setUpContainerMock(): void
    {
        $parameters = [
            [AuthorizationPolicyRegistry::class, AuthorizationPolicyRegistry::class],
            [AuthorizationRequirementHandlerRegistry::class, AuthorizationRequirementHandlerRegistry::class],
            [IUserAccessor::class, RequestPropertyUserAccessor::class],
            [IAuthority::class, Authority::class]
        ];

        foreach ($parameters as $parameter) {
            $this->container->shouldReceive('bindInstance')
                ->with($parameter[0], Mockery::type($parameter[1]));
        }
    }
}
