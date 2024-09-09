<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authorization\Tests\RequirementHandlers;

use Aphiria\Authorization\AuthorizationContext;
use Aphiria\Authorization\RequirementHandlers\RolesRequirement;
use Aphiria\Authorization\RequirementHandlers\RolesRequirementHandler;
use Aphiria\Security\Claim;
use Aphiria\Security\ClaimType;
use Aphiria\Security\IIdentity;
use Aphiria\Security\IPrincipal;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RolesRequirementHandlerTest extends TestCase
{
    private RolesRequirementHandler $requirementHandler;

    protected function setUp(): void
    {
        $this->requirementHandler = new RolesRequirementHandler();
    }

    public static function getUsersWithMatchingRoles(): array
    {
        $userWithSingleMatchingRole = new class () implements IPrincipal {
            public array $claims = [];
            public array $identities = [];
            public ?IIdentity $primaryIdentity = null;

            public function __construct()
            {
                $this->claims = [new Claim(ClaimType::Role, 'admin', 'example.com')];
            }

            public function addIdentity(IIdentity $identity): void
            {
            }

            public function addManyIdentities(array $identities): void
            {
            }

            public function filterClaims(ClaimType|string $type): array
            {
                return [new Claim(ClaimType::Role, 'admin', 'example.com')];
            }

            public function hasClaim(ClaimType|string $type, mixed $value): bool
            {
                return false;
            }

            public function mergeIdentities(IPrincipal $user, bool $includeUnauthenticatedIdentities = false): IPrincipal
            {
                return $this;
            }
        };
        $userWithManyRolesIncludingMatchingOne = new class () implements IPrincipal {
            public array $claims = [];
            public array $identities = [];
            public ?IIdentity $primaryIdentity = null;

            public function __construct()
            {
                $this->claims = [
                    new Claim(ClaimType::Role, 'admin', 'example.com'),
                    new Claim(ClaimType::Role, 'dev', 'example.com')
                ];
            }

            public function addIdentity(IIdentity $identity): void
            {
            }

            public function addManyIdentities(array $identities): void
            {
            }

            public function filterClaims(ClaimType|string $type): array
            {
                return [
                    new Claim(ClaimType::Role, 'admin', 'example.com'),
                    new Claim(ClaimType::Role, 'dev', 'example.com')
                ];
            }

            public function hasClaim(ClaimType|string $type, mixed $value): bool
            {
                return false;
            }

            public function mergeIdentities(IPrincipal $user, bool $includeUnauthenticatedIdentities = false): IPrincipal
            {
                return $this;
            }
        };

        return [
            [$userWithSingleMatchingRole],
            [$userWithManyRolesIncludingMatchingOne]
        ];
    }

    public static function getUsersWithNoMatchingRoles(): array
    {
        $userWithNoRoles = new class () implements IPrincipal {
            public array $claims = [];
            public array $identities = [];
            public ?IIdentity $primaryIdentity = null;

            public function addIdentity(IIdentity $identity): void
            {
            }

            public function addManyIdentities(array $identities): void
            {
            }

            public function filterClaims(ClaimType|string $type): array
            {
                return [];
            }

            public function hasClaim(ClaimType|string $type, mixed $value): bool
            {
                return false;
            }

            public function mergeIdentities(IPrincipal $user, bool $includeUnauthenticatedIdentities = false): IPrincipal
            {
                return $this;
            }
        };
        $userWithNoMatchingRoles = new class () implements IPrincipal {
            public array $claims = [];
            public array $identities = [];
            public ?IIdentity $primaryIdentity = null;

            public function __construct()
            {
                $this->claims = [new Claim(ClaimType::Role, 'unused', 'example.com')];
            }

            public function addIdentity(IIdentity $identity): void
            {
            }

            public function addManyIdentities(array $identities): void
            {
            }

            public function filterClaims(ClaimType|string $type): array
            {
                return [new Claim(ClaimType::Role, 'unused', 'example.com')];
            }

            public function hasClaim(ClaimType|string $type, mixed $value): bool
            {
                return false;
            }

            public function mergeIdentities(IPrincipal $user, bool $includeUnauthenticatedIdentities = false): IPrincipal
            {
                return $this;
            }
        };

        return [
            [$userWithNoRoles],
            [$userWithNoMatchingRoles]
        ];
    }

    public function testPassingInvalidRequirementTypeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Requirement must be of type ' . RolesRequirement::class . ', ' . $this::class . ' given');
        $user = $this->createMock(IPrincipal::class);
        $context = new AuthorizationContext($user, [$this], null);
        /** @psalm-suppress InvalidArgument We're explicitly testing an invalid value */
        $this->requirementHandler->handle($user, $this, $context);
    }

    /**
     * @param IPrincipal $user The user with matching role claims
     */
    #[DataProvider('getUsersWithMatchingRoles')]
    public function testUserWithMatchingRoleClaimPasses(IPrincipal $user): void
    {
        $roleRequirement = new RolesRequirement('admin');
        $context = new AuthorizationContext($user, [$roleRequirement], null);
        $this->requirementHandler->handle($user, $roleRequirement, $context);
        $this->assertTrue($context->allRequirementsPassed());
        $this->assertCount(0, $context->pendingRequirements);
    }

    /**
     * @param IPrincipal $user The user with no matching roles
     */
    #[DataProvider('getUsersWithNoMatchingRoles')]
    public function testUserWithNoMatchingRoleClaimsDoesNotPass(IPrincipal $user): void
    {
        $roleRequirement = new RolesRequirement('admin');
        $context = new AuthorizationContext($user, [$roleRequirement], null);
        $this->requirementHandler->handle($user, $roleRequirement, $context);
        $this->assertFalse($context->allRequirementsPassed());
        $this->assertSame([$roleRequirement], $context->pendingRequirements->toArray());
    }
}
