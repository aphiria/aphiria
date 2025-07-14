<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests\Controllers\Mocks;

use Aphiria\Api\Controllers\Controller as BaseController;
use Aphiria\Net\Http\HttpStatusCode;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\Response;
use Aphiria\Net\Http\StringBody;
use RuntimeException;

/**
 * Defines a mock controller for use in testing
 */
class ControllerWithEndpoints extends BaseController
{
    /**
     * Mocks a method that takes in no parameters
     *
     * @return Response The method name
     */
    public function noParameters(): IResponse
    {
        return new Response(body: new StringBody('noParameters'));
    }

    /**
     * Mocks a method with an object parameter
     *
     * @param User $user The user
     * @return IResponse The response
     */
    public function objectParameter(User $user): IResponse
    {
        return $this->createResponseWithBody("id:{$user->id}, email:{$user->email}");
    }

    /**
     * Mocks a method that returns a POPO
     *
     * @return User The POPO
     */
    public function popo(): User
    {
        return new User(123, 'foo@bar.com');
    }

    /**
     * Mocks a method with a string parameter
     *
     * @param string $foo The string
     * @return IResponse The response
     */
    public function stringParameter(string $foo): IResponse
    {
        return $this->createResponseWithBody($foo);
    }

    /**
     * Mocks a method that throws an exception
     *
     * @throws RuntimeException Thrown every time
     */
    public function throwsException(): void
    {
        throw new RuntimeException('Testing controller method that throws exception');
    }

    /**
     * Mocks a method with a void return type;
     */
    public function voidReturnType(): void
    {
        // Don't do anything
    }

    /**
     * Mocks a protected method for use in testing
     *
     * @return Response The name of the method
     */
    protected function protectedMethod(): IResponse
    {
        return $this->createResponseWithBody('protectedMethod');
    }

    /**
     * Creates a response with the input body
     *
     * @param string $body The body of the response
     * @return Response The response
     */
    private function createResponseWithBody(string $body): Response
    {
        return new Response(HttpStatusCode::Ok, body: new StringBody($body));
    }

    /**
     * Mocks a private method for use in testing
     *
     * @return Response The name of the method
     */
    private function privateMethod(): IResponse
    {
        return $this->createResponseWithBody('privateMethod');
    }
}
