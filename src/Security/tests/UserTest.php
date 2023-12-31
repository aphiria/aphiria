<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Security\Tests;

use Aphiria\Security\Claim;
use Aphiria\Security\ClaimType;
use Aphiria\Security\Identity;
use Aphiria\Security\IIdentity;
use Aphiria\Security\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testAddingIdentityAddsIt(): void
    {
        $user = new User([]);
        $identity1 = new Identity([], 'http://example.com');
        $user->addIdentity($identity1);
        $this->assertSame([$identity1], $user->getIdentities());
        $identity2 = new Identity([], 'http://example.com');
        $user->addIdentity($identity2);
        $this->assertSame([$identity1, $identity2], $user->getIdentities());
    }
    public function testAddingIdentityCanResetPrimaryIdentity(): void
    {
        // This will return the last one
        /**
         * @psalm-suppress MixedInferredReturnType The closure will always return an identity
         * @psalm-suppress MixedReturnStatement Ditto
         */
        $primaryIdentitySelector = static fn (array $identities): ?IIdentity => \count($identities) === 0 ? null : $identities[\count($identities) - 1];
        $user = new User([], $primaryIdentitySelector);
        $this->assertNull($user->getPrimaryIdentity());
        $identity1 = new Identity([], 'http://example.com');
        $user->addIdentity($identity1);
        $this->assertSame($identity1, $user->getPrimaryIdentity());
        $identity2 = new Identity([], 'http://example.com');
        $user->addIdentity($identity2);
        $this->assertSame($identity2, $user->getPrimaryIdentity());
    }

    public function testAddingManyIdentitiesAddsThem(): void
    {
        $user = new User([]);
        $identity1 = new Identity([], 'http://example.com');
        $identity2 = new Identity([], 'http://example.com');
        $user->addManyIdentities([$identity1, $identity2]);
        $this->assertSame([$identity1, $identity2], $user->getIdentities());
        $identity3 = new Identity([], 'http://example.com');
        $user->addManyIdentities([$identity3]);
        $this->assertSame([$identity1, $identity2, $identity3], $user->getIdentities());
    }

    public function testAddingManyIdentitiesCanResetPrimaryIdentity(): void
    {
        // This will return the last one
        /**
         * @psalm-suppress MixedInferredReturnType The closure will always return an identity
         * @psalm-suppress MixedReturnStatement Ditto
         */
        $primaryIdentitySelector = static fn (array $identities): ?IIdentity => \count($identities) === 0 ? null : $identities[\count($identities) - 1];
        $user = new User([], $primaryIdentitySelector);
        $this->assertNull($user->getPrimaryIdentity());
        $identity1 = new Identity([], 'http://example.com');
        $identity2 = new Identity([], 'http://example.com');
        $user->addManyIdentities([$identity1, $identity2]);
        $this->assertSame($identity2, $user->getPrimaryIdentity());
    }

    public function testFilteringClaimsOnlyReturnsClaimsOfThatType(): void
    {
        $identity1Claims = [
            new Claim('foo', 'bar', 'http://example.com'),
            new Claim('foo', 'baz', 'http://example.com'),
            new Claim('baz', 'quz', 'http://example.com')
        ];
        $identity2Claims = [
            new Claim('foo', 'quz', 'http://example.com'),
            new Claim('foo', 'qux', 'http://example.com'),
            new Claim('baz', 'qiz', 'http://example.com')
        ];
        $user = new User([new Identity($identity1Claims), new Identity($identity2Claims)]);
        $this->assertSame(
            [$identity1Claims[0], $identity1Claims[1], $identity2Claims[0], $identity2Claims[1]],
            $user->filterClaims('foo')
        );
    }

    public function testGettingClaimsWithoutFilterReturnsAllClaims(): void
    {
        $identity1Claims = [
            new Claim('foo', 'bar', 'http://example.com'),
            new Claim('foo', 'baz', 'http://example.com'),
            new Claim('baz', 'quz', 'http://example.com')
        ];
        $identity2Claims = [
            new Claim('foo', 'quz', 'http://example.com'),
            new Claim('foo', 'qux', 'http://example.com'),
            new Claim('baz', 'qiz', 'http://example.com')
        ];
        $user = new User([new Identity($identity1Claims), new Identity($identity2Claims)]);
        $this->assertSame([...$identity1Claims, ...$identity2Claims], $user->getClaims());
    }

    public function testGettingIdentitiesReturnsOnesSetInConstructor(): void
    {
        $identities = [new Identity([], 'http://example.com'), new Identity([], 'http://example.com')];
        $user = new User($identities);
        $this->assertSame($identities, $user->getIdentities());
    }

    public function testHasClaimReturnsWhetherOrNotAnyIdentityHasClaim(): void
    {
        // Test with string types
        $identity = new Identity([new Claim('foo', 'bar', 'http://example.com')]);
        $user = new User([$identity]);
        $this->assertTrue($user->hasClaim('foo', 'bar'));
        $this->assertFalse($user->hasClaim('foo', 'baz'));
        $this->assertFalse($user->hasClaim('doesNotExist', 'bar'));

        // Test with enum types
        $identity = new Identity([new Claim(ClaimType::Actor, 'bar', 'http://example.com')]);
        $user = new User([$identity]);
        $this->assertTrue($user->hasClaim(ClaimType::Actor, 'bar'));
        $this->assertFalse($user->hasClaim(ClaimType::Actor, 'baz'));
        $this->assertFalse($user->hasClaim(ClaimType::NameIdentifier, 'bar'));

        // Test with multiple identities
        $user = new User([
            new Identity([new Claim('foo', 'bar', 'http://example.com')]),
            new Identity([new Claim('baz', 'quz', 'http://example.com')])
        ]);
        $this->assertTrue($user->hasClaim('foo', 'bar'));
        $this->assertTrue($user->hasClaim('baz', 'quz'));
        $this->assertFalse($user->hasClaim('baz', 'qiz'));
    }

    public function testMergingIdentitiesAndIncludingUnauthenticatedIdentitiesIncludesThem(): void
    {
        $user1Identities = [new Identity([new Claim('foo', 'bar', 'example.com')], 'authScheme')];
        $user2Identities = [
            new Identity([new Claim('baz', 'quz', 'example.com')], 'authScheme'),
            new Identity([new Claim('qux', 'blah', 'example.com')])
        ];
        $user1 = new User($user1Identities);
        $user2 = new User($user2Identities);
        $mergedUser = $user1->mergeIdentities($user2, true);
        $mergedIdentities = $mergedUser->getIdentities();
        $this->assertCount(3, $mergedIdentities);
        $this->assertSame(
            [...$user1Identities, ...$user2Identities],
            $mergedIdentities
        );
    }

    public function testMergingIdentitiesExcludesUnauthenticatedIdentities(): void
    {
        $user1Identities = [new Identity([new Claim('foo', 'bar', 'example.com')], 'authScheme')];
        $user2Identities = [
            new Identity([new Claim('baz', 'quz', 'example.com')], 'authScheme'),
            new Identity([new Claim('qux', 'blah', 'example.com')])
        ];
        $user1 = new User($user1Identities);
        $user2 = new User($user2Identities);
        $mergedUser = $user1->mergeIdentities($user2);
        $mergedIdentities = $mergedUser->getIdentities();
        $this->assertCount(2, $mergedIdentities);
        $this->assertSame(
            [...$user1Identities, $user2Identities[0]],
            $mergedIdentities
        );
    }

    public function testPrimaryIdentityIsFirstOneByDefault(): void
    {
        $identities = [
            new Identity([], 'http://example.com'),
            new Identity([], 'http://example.com')
        ];
        $user = new User($identities);
        $this->assertSame($identities[0], $user->getPrimaryIdentity());
        $user->addIdentity(new Identity([], 'http://example.com'));
        $this->assertSame($identities[0], $user->getPrimaryIdentity());
    }

    public function testPrimaryIdentityRespectsSelectorIfSpecified(): void
    {
        // This will return the last one
        /**
         * @psalm-suppress MixedInferredReturnType The closure will always return an identity
         * @psalm-suppress MixedReturnStatement Ditto
         */
        $primaryIdentitySelector = static fn (array $identities): ?IIdentity => \count($identities) === 0 ? null : $identities[\count($identities) - 1];
        $user = new User([], $primaryIdentitySelector);
        $this->assertNull($user->getPrimaryIdentity());
        $identity1 = new Identity([], 'http://example.com');
        $user->addIdentity($identity1);
        $this->assertSame($identity1, $user->getPrimaryIdentity());
        $identity2 = new Identity([], 'http://example.com');
        $user->addIdentity($identity2);
        $this->assertSame($identity2, $user->getPrimaryIdentity());
    }

    public function testSettingSingleIdentityInConstructorConvertsItToArray(): void
    {
        $identity = new Identity([], 'http://example.com');
        $user = new User($identity);
        $this->assertSame([$identity], $user->getIdentities());
    }
}
