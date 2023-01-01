<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Security\Tests;

use Aphiria\Security\Claim;
use Aphiria\Security\ClaimType;
use Aphiria\Security\ClaimsIdentity;
use Aphiria\Security\IIdentity;
use Aphiria\Security\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
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
        $identity1 = new ClaimsIdentity([], 'http://example.com');
        $user->addIdentity($identity1);
        $this->assertSame($identity1, $user->getPrimaryIdentity());
        $identity2 = new ClaimsIdentity([], 'http://example.com');
        $user->addIdentity($identity2);
        $this->assertSame($identity2, $user->getPrimaryIdentity());
    }

    public function testAddingIdentityAddsIt(): void
    {
        $user = new User([]);
        $identity1 = new ClaimsIdentity([], 'http://example.com');
        $user->addIdentity($identity1);
        $this->assertSame([$identity1], $user->getIdentities());
        $identity2 = new ClaimsIdentity([], 'http://example.com');
        $user->addIdentity($identity2);
        $this->assertSame([$identity1, $identity2], $user->getIdentities());
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
        $identity1 = new ClaimsIdentity([], 'http://example.com');
        $identity2 = new ClaimsIdentity([], 'http://example.com');
        $user->addManyIdentities([$identity1, $identity2]);
        $this->assertSame($identity2, $user->getPrimaryIdentity());
    }

    public function testAddingManyIdentitiesAddsThem(): void
    {
        $user = new User([]);
        $identity1 = new ClaimsIdentity([], 'http://example.com');
        $identity2 = new ClaimsIdentity([], 'http://example.com');
        $user->addManyIdentities([$identity1, $identity2]);
        $this->assertSame([$identity1, $identity2], $user->getIdentities());
        $identity3 = new ClaimsIdentity([], 'http://example.com');
        $user->addManyIdentities([$identity3]);
        $this->assertSame([$identity1, $identity2, $identity3], $user->getIdentities());
    }

    public function testGettingClaimsWithFilterOnlyReturnsClaimsOfThatType(): void
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
        $user = new User([new ClaimsIdentity($identity1Claims), new ClaimsIdentity($identity2Claims)]);
        $this->assertSame(
            [$identity1Claims[0], $identity1Claims[1], $identity2Claims[0], $identity2Claims[1]],
            $user->getClaims('foo')
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
        $user = new User([new ClaimsIdentity($identity1Claims), new ClaimsIdentity($identity2Claims)]);
        $this->assertSame([...$identity1Claims, ...$identity2Claims], $user->getClaims());
    }

    public function testGettingIdentitiesReturnsOnesSetInConstructor(): void
    {
        $identities = [new ClaimsIdentity([], 'http://example.com'), new ClaimsIdentity([], 'http://example.com')];
        $user = new User($identities);
        $this->assertSame($identities, $user->getIdentities());
    }

    public function testHasClaimReturnsWhetherOrNotAnyIdentityHasClaim(): void
    {
        // Test with string types
        $identity = new ClaimsIdentity([new Claim('foo', 'bar', 'http://example.com')]);
        $user = new User([$identity]);
        $this->assertTrue($user->hasClaim('foo', 'bar'));
        $this->assertFalse($user->hasClaim('foo', 'baz'));
        $this->assertFalse($user->hasClaim('doesNotExist', 'bar'));

        // Test with enum types
        $identity = new ClaimsIdentity([new Claim(ClaimType::Actor, 'bar', 'http://example.com')]);
        $user = new User([$identity]);
        $this->assertTrue($user->hasClaim(ClaimType::Actor, 'bar'));
        $this->assertFalse($user->hasClaim(ClaimType::Actor, 'baz'));
        $this->assertFalse($user->hasClaim(ClaimType::NameIdentifier, 'bar'));

        // Test with multiple identities
        $user = new User([
            new ClaimsIdentity([new Claim('foo', 'bar', 'http://example.com')]),
            new ClaimsIdentity([new Claim('baz', 'quz', 'http://example.com')])
        ]);
        $this->assertTrue($user->hasClaim('foo', 'bar'));
        $this->assertTrue($user->hasClaim('baz', 'quz'));
        $this->assertFalse($user->hasClaim('baz', 'qiz'));
    }

    public function testPrimaryIdentityIsFirstOneByDefault(): void
    {
        $identities = [
            new ClaimsIdentity([], 'http://example.com'),
            new ClaimsIdentity([], 'http://example.com')
        ];
        $user = new User($identities);
        $this->assertSame($identities[0], $user->getPrimaryIdentity());
        $user->addIdentity(new ClaimsIdentity([], 'http://example.com'));
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
        $identity1 = new ClaimsIdentity([], 'http://example.com');
        $user->addIdentity($identity1);
        $this->assertSame($identity1, $user->getPrimaryIdentity());
        $identity2 = new ClaimsIdentity([], 'http://example.com');
        $user->addIdentity($identity2);
        $this->assertSame($identity2, $user->getPrimaryIdentity());
    }

    public function testSettingSingleIdentityInConstructorConvertsItToArray(): void
    {
        $identity = new ClaimsIdentity([], 'http://example.com');
        $user = new User($identity);
        $this->assertSame([$identity], $user->getIdentities());
    }
}
