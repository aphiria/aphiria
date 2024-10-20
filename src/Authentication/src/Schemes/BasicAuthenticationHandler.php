<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
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
            $authorizationHeaderValue = (string)$request->headers->getFirst('Authorization');
        } catch (OutOfBoundsException $ex) {
            return AuthenticationResult::fail(
                new MissingAuthenticationDataException('Missing authorization header', previous: $ex),
                $scheme->name
            );
        }

        $explodedAuthorizationHeaderValue = \explode(' ', \trim($authorizationHeaderValue));

        if (\count($explodedAuthorizationHeaderValue) !== 2) {
            return AuthenticationResult::fail(
                new InvalidArgumentException('Authorization header value was invalid'),
                $scheme->name
            );
        }

        [$authType, $base64EncodedCredentials] = $explodedAuthorizationHeaderValue;

        if (\strtolower($authType) !== 'basic') {
            return AuthenticationResult::fail(
                new MissingAuthenticationDataException('Request did not use basic authentication'),
                $scheme->name
            );
        }

        $base64DecodedCredentials = \base64_decode($base64EncodedCredentials, true);

        if ($base64DecodedCredentials === false) {
            return AuthenticationResult::fail(
                new InvalidArgumentException('Authorization header did not contain valid base64-encoded value'),
                $scheme->name
            );
        }

        $explodedCredentials = \explode(':', $base64DecodedCredentials);

        if (\count($explodedCredentials) !== 2) {
            return AuthenticationResult::fail(
                new InvalidArgumentException('Authorization header did not contain a base64-encoded username:password value'),
                $scheme->name
            );
        }

        [$username, $password] = $explodedCredentials;

        return $this->createAuthenticationResultFromCredentials($username, $password, $request, $scheme);
    }

    /**
     * @inheritdoc
     * @param AuthenticationScheme<BasicAuthenticationOptions> $scheme
     */
    public function challenge(IRequest $request, IResponse $response, AuthenticationScheme $scheme): void
    {
        $realmValue = $scheme->options->realm === null ? '' : " realm=\"{$scheme->options->realm}\"";
        $response->headers->add('Www-Authenticate', 'Basic' . $realmValue);
        $response->statusCode = HttpStatusCode::Unauthorized;
    }

    /**
     * @inheritdoc
     * @param AuthenticationScheme<BasicAuthenticationOptions> $scheme
     */
    public function forbid(IRequest $request, IResponse $response, AuthenticationScheme $scheme): void
    {
        $response->statusCode = HttpStatusCode::Forbidden;
    }

    /**
     * Creates an authentication result from credentials
     *
     * @param string $username The username
     * @param string $password The password
     * @param IRequest $request The current request
     * @param AuthenticationScheme<BasicAuthenticationOptions> $scheme The authentication scheme used
     * @return AuthenticationResult The authentication result
     */
    abstract protected function createAuthenticationResultFromCredentials(string $username, string $password, IRequest $request, AuthenticationScheme $scheme): AuthenticationResult;
}
