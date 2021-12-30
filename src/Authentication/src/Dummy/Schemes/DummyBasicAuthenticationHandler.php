<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication\Dummy\Schemes;

use Aphiria\Authentication\AuthenticationResult;
use Aphiria\Authentication\AuthenticationScheme;
use Aphiria\Authentication\Schemes\BasicAuthenticationHandler;
use Aphiria\Security\Claim;
use Aphiria\Security\ClaimType;
use Aphiria\Security\Identity;
use Aphiria\Security\User;

/**
 * Defines a dummy basic authentication handler
 *
 * TODO: DO NOT USE THIS!!! IT'S JUST A HORRIBLY INSECURE PROOF OF CONCEPT!!!!!!
 * @codeCoverageIgnore
 */
final class DummyBasicAuthenticationHandler extends BasicAuthenticationHandler
{
    /** @var array<string, array{id: int, email: string, hashedPassword: string, roles: list<string>}> The hard-coded user database */
    private static array $userDb = [
        // Password is 'foo'
        'dave@aphiria.com' => ['id' => 1, 'email' => 'dave@aphiria.com', 'hashedPassword' => '$argon2id$v=19$m=65536,t=4,p=1$RHRVTjJMeUdIY1M2NG93ZA$gwftkwVH0vCo+GjWVeLIwpdbZMctBlrJ3kDAsB9MYAM', 'roles' => ['admin']]
    ];

    /**
     * @inheritdoc
     */
    protected function createAuthenticationResultFromCredentials(string $username, string $password, AuthenticationScheme $scheme): AuthenticationResult
    {
        $user = self::$userDb[\strtolower($username)] ?? null;

        if ($user === null) {
            return AuthenticationResult::fail('Could not find user with that username');
        }

        if (!\password_verify($password, $user['hashedPassword'])) {
            return AuthenticationResult::fail('Incorrect password');
        }

        $claimsIssuer = $scheme->options->claimsIssuer ?? $scheme->name;
        $claims = [
            new Claim(ClaimType::NameIdentifier, $user['id'], $claimsIssuer),
            new Claim(ClaimType::Name, $user['email'], $claimsIssuer),
            new Claim(ClaimType::Email, $user['email'], $claimsIssuer)
        ];

        foreach ($user['roles'] as $role) {
            $claims[] = new Claim(ClaimType::Role, $role, $claimsIssuer);
        }

        return AuthenticationResult::pass(new User(new Identity($claims, $scheme->name)));
    }
}
