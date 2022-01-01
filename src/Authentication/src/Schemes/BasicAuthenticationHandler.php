<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication\Schemes;

use Aphiria\Authentication\AuthenticationResult;
use Aphiria\Authentication\AuthenticationScheme;
use Aphiria\Authentication\MissingAuthenticationDataException;
use Aphiria\Net\Http\HttpStatusCode;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use InvalidArgumentException;
use OutOfBoundsException;

/**
 * Defines a basic authentication scheme handler
 *
 * @implements IAuthenticationSchemeHandler<BasicAuthenticationOptions>
 */
abstract class BasicAuthenticationHandler implements IAuthenticationSchemeHandler
{
    /**
     * @inheritdoc
     * @param AuthenticationScheme<BasicAuthenticationOptions> $scheme
     */
    public function authenticate(IRequest $request, AuthenticationScheme $scheme): AuthenticationResult
    {
        try {
            $authorizationHeaderValue = (string)$request->getHeaders()->getFirst('Authorization');
        } catch (OutOfBoundsException $ex) {
            return AuthenticationResult::fail(new MissingAuthenticationDataException('Missing authorization header', previous: $ex));
        }

        $explodedAuthorizationHeaderValue = \explode(' ', \trim($authorizationHeaderValue));

        if (\count($explodedAuthorizationHeaderValue) !== 2) {
            return AuthenticationResult::fail(new InvalidArgumentException('Authorization header value was invalid'));
        }

        [$authType, $base64EncodedCredentials] = $explodedAuthorizationHeaderValue;

        if (\strtolower($authType) !== 'basic') {
            return AuthenticationResult::fail(new MissingAuthenticationDataException('Request did not use basic authentication'));
        }

        $base64DecodedCredentials = \base64_decode($base64EncodedCredentials, true);

        if ($base64DecodedCredentials === false) {
            return AuthenticationResult::fail(new InvalidArgumentException('Authorization header did not contain valid base64-encoded value'));
        }

        $explodedCredentials = \explode(':', $base64DecodedCredentials);

        if (\count($explodedCredentials) !== 2) {
            return AuthenticationResult::fail(new InvalidArgumentException('Authorization header did not contain a base64-encoded username:password value'));
        }

        [$username, $password] = $explodedCredentials;

        return $this->createAuthenticationResultFromCredentials($username, $password, $scheme);
    }

    /**
     * @inheritdoc
     * @param AuthenticationScheme<BasicAuthenticationOptions> $scheme
     */
    public function challenge(IRequest $request, IResponse $response, AuthenticationScheme $scheme): void
    {
        $realmValue = $scheme->options->realm === null ? '' : " realm=\"{$scheme->options->realm}\"";
        $response->getHeaders()->add('Www-Authenticate', 'Basic' . $realmValue);
        $response->setStatusCode(HttpStatusCode::Unauthorized);
    }

    /**
     * @inheritdoc
     * @param AuthenticationScheme<BasicAuthenticationOptions> $scheme
     */
    public function forbid(IRequest $request, IResponse $response, AuthenticationScheme $scheme): void
    {
        $response->setStatusCode(HttpStatusCode::Forbidden);
    }

    /**
     * Creates an authentication result from credentials
     *
     * @param string $username The username
     * @param string $password The password
     * @param AuthenticationScheme<BasicAuthenticationOptions> $scheme The authentication scheme used
     * @return AuthenticationResult The authentication result
     */
    abstract protected function createAuthenticationResultFromCredentials(string $username, string $password, AuthenticationScheme $scheme): AuthenticationResult;
}
