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
use Aphiria\Authentication\Schemes\CookieAuthenticationHandler;
use Aphiria\Authentication\Schemes\CookieAuthenticationOptions;
use Aphiria\Security\Claim;
use Aphiria\Security\ClaimType;
use Aphiria\Security\Identity;
use Aphiria\Security\IPrincipal;
use Aphiria\Security\User;
use JsonException;

/**
 * Defines a dummy cookie authentication handler
 *
 * TODO: DO NOT USE THIS!!! IT'S JUST A HORRIBLY INSECURE PROOF OF CONCEPT!!!!!!
 * @codeCoverageIgnore
 */
final class DummyCookieAuthenticationHandler extends CookieAuthenticationHandler
{
    /**
     * @inheritdoc
     * @param AuthenticationScheme<CookieAuthenticationOptions> $scheme
     * @throws JsonException Thrown if there was an error decoding the cookie
     */
    protected function createAuthenticationResultFromCookie(string $cookieValue, AuthenticationScheme $scheme): AuthenticationResult
    {
        $claimsIssuer = $scheme->options->claimsIssuer ?? $scheme->name;
        /** @var array{userId: int, roles: list<string>} $token */
        $token = \json_decode($cookieValue, true, flags: \JSON_THROW_ON_ERROR);
        $claims = [new Claim(ClaimType::NameIdentifier, $token['userId'], $claimsIssuer)];

        foreach ($token['roles'] as $role) {
            $claims[] = new Claim(ClaimType::Role, (string)$role, $claimsIssuer);
        }

        $identity = new Identity($claims, $scheme->name);
        $user = new User($identity);

        return AuthenticationResult::pass($user);
    }

    /**
     * @inheritdoc
     * @param AuthenticationScheme<CookieAuthenticationOptions> $scheme
     * @throws JsonException Thrown if there was an error encoding the JSON
     */
    protected function getCookieValueFromUser(IPrincipal $user, AuthenticationScheme $scheme): string|int|float
    {
        $roles = [];

        foreach ($user->getClaims(ClaimType::Role) as $roleClaim) {
            $roles[] = (string)$roleClaim->value;
        }

        return \json_encode(['userId' => $user->getPrimaryIdentity()?->getNameIdentifier(), 'roles' => $roles], flags: \JSON_THROW_ON_ERROR);
    }
}
