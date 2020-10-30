<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests\Controllers\Mocks;

use Aphiria\Api\Controllers\Controller as BaseController;
use Aphiria\Net\Http\HttpStatusCodes;
use Aphiria\Net\Http\IRequest;
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
     * Mocks a method with an array parameter
     *
     * @param array $foo The array
     * @return IResponse The response
     */
    public function arrayParameter(array $foo): IResponse
    {
        return $this->createResponseWithBody(\json_encode($foo));
    }

    /**
     * Mocks a method with a bool parameter
     *
     * @param bool $foo The bool
     * @return IResponse The response
     */
    public function boolParameter(bool $foo): IResponse
    {
        return $this->createResponseWithBody((string)$foo);
    }

    /**
     * Mocks a method with a callable parameter
     *
     * @param callable $foo The float
     * @return IResponse The response
     * @psalm-suppress InvalidCast This cast is actually allowed
     */
    public function callableParameter(callable $foo): IResponse
    {
        return $this->createResponseWithBody((string)$foo);
    }

    /**
     * Mocks a method with a parameter with a default value
     *
     * @param string $foo The string
     * @return IResponse The response
     */
    public function defaultValueParameter(string $foo = 'bar'): IResponse
    {
        return $this->createResponseWithBody($foo);
    }

    /**
     * Mocks a method with a float parameter
     *
     * @param float $foo The float
     * @return IResponse The response
     */
    public function floatParameter(float $foo): IResponse
    {
        return $this->createResponseWithBody((string)$foo);
    }

    /**
     * Gets the current request (for use in tests)
     *
     * @return IRequest|null The current request, or null if it isn't set yet
     */
    public function getRequest(): ?IRequest
    {
        return $this->request;
    }

    /**
     * Mocks a method with an int parameter
     *
     * @param int $foo The int
     * @return IResponse The response
     */
    public function intParameter(int $foo): IResponse
    {
        return $this->createResponseWithBody((string)$foo);
    }

    /**
     * Mocks a method that takes in no parameters
     *
     * @return Response The method name
     */
    public function noParameters(): IResponse
    {
        return $this->createResponseWithBody('noParameters');
    }

    /**
     * Mocks a method with a parameter with no type hint
     *
     * @param mixed $foo The parameter to use in the response
     * @return Response The response
     */
    public function noTypeHintParameter($foo): IResponse
    {
        return $this->createResponseWithBody((string)$foo);
    }

    /**
     * Mocks a method that takes in a nullable object parameter
     *
     * @param User|null $user The user
     * @return Response The response
     */
    public function nullableObjectParameter(?User $user): IResponse
    {
        return $this->createResponseWithBody($user === null ? 'null' : 'notnull');
    }

    /**
     * Mocks a method that takes in a nullable scalar parameter
     *
     * @param int|null $foo The nullable parameter
     * @return Response The response
     */
    public function nullableScalarParameter(?int $foo): IResponse
    {
        return $this->createResponseWithBody($foo === null ? 'null' : 'notnull');
    }

    /**
     * Mocks a method with an object parameter
     *
     * @param User $user The user
     * @return IResponse The response
     */
    public function objectParameter(User $user): IResponse
    {
        return $this->createResponseWithBody("id:{$user->getId()}, email:{$user->getEmail()}");
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
     * Mocks a method that does not return anything
     */
    public function returnsNothing(): void
    {
        // Don't do anything
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
        return new Response(HttpStatusCodes::OK, null, new StringBody($body));
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
