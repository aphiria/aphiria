<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication\Dummy;

use Aphiria\Api\Controllers\Controller;
use Aphiria\Authentication\Attributes\Authenticate;
use Aphiria\Authentication\IAuthenticator;
use Aphiria\Authentication\UnsupportedAuthenticationHandlerException;
use Aphiria\Authorization\Attributes\AuthorizeRoles;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\IResponse;
use Aphiria\Routing\Attributes\Get;
use Aphiria\Routing\Attributes\Post;
use Exception;

/**
 * Defines a dummy authentication controller
 *
 * TODO: DELETE THIS - IT'S JUST A PROOF OF CONCEPT AND IS WHOLLY INSECURE
 * @codeCoverageIgnore
 */
class DummyAuthenticationController extends Controller
{
    /**
     * @param IAuthenticator $authenticator The authenticator to use
     */
    public function __construct(private readonly IAuthenticator $authenticator)
    {
    }

    /**
     * Attempts to log in a user
     *
     * @return IResponse The logged in response
     * @throws UnsupportedAuthenticationHandlerException Thrown if the authentication handler did not support login
     * @throws HttpException Thrown if there was an error negotiating content
     * @throws Exception Thrown if there was an error authenticating the user
     */
    #[Post('login')]
    public function logIn(): IResponse
    {
        $authResult = $this->authenticator->authenticate($this->request, 'basic');

        if (!$authResult->passed) {
            throw $authResult->failure;
        }

        $response = $this->noContent();
        $this->authenticator->logIn($authResult->user, $this->request, $response);

        return $response;
    }

    /**
     * Logs out a user
     *
     * @return IResponse The logged out response
     * @throws UnsupportedAuthenticationHandlerException Thrown if the authentication handler did not support login
     * @throws HttpException Thrown if there was an error negotiating content
     */
    #[Post('logout')]
    public function logOut(): IResponse
    {
        $response = $this->noContent();
        $this->authenticator->logOut($this->request, $response);

        return $response;
    }

    /**
     * Defines an endpoint that requires authentication by a specific scheme
     *
     * @return IResponse The response
     * @throws HttpException Thrown if there was an error negotiating content
     */
    #[Get('needs-auth')]
    #[Authenticate(schemeName: 'cookie')]
    #[AuthorizeRoles('admin', authenticationSchemeNames: 'cookie')]
    public function needsAuthentication(): IResponse
    {
        return $this->ok();
    }
}
