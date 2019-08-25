<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests\Controllers\Mocks;

use Aphiria\Api\Controllers\Controller as BaseController;
use Aphiria\Net\Http\HttpStatusCodes;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use Aphiria\Net\Http\Response;
use Aphiria\Net\Http\StringBody;
use function json_encode;
use RuntimeException;

/**
 * Defines a mock controller for use in testing
 */
class Controller extends BaseController
{
    /**
     * Mocks a method with an array parameter
     *
     * @param array $foo The array
     * @return IHttpResponseMessage The response
     */
    public function arrayParameter(array $foo): IHttpResponseMessage
    {
        return $this->createResponseWithBody(json_encode($foo));
    }

    /**
     * Mocks a method with a bool parameter
     *
     * @param bool $foo The bool
     * @return IHttpResponseMessage The response
     */
    public function boolParameter(bool $foo): IHttpResponseMessage
    {
        return $this->createResponseWithBody((string)$foo);
    }

    /**
     * Mocks a method with a parameter with a default value
     *
     * @param string $foo The string
     * @return IHttpResponseMessage The response
     */
    public function defaultValueParameter(string $foo = 'bar'): IHttpResponseMessage
    {
        return $this->createResponseWithBody($foo);
    }
    /**
     * Mocks a method with a float parameter
     *
     * @param float $foo The float
     * @return IHttpResponseMessage The response
     */
    public function floatParameter(float $foo): IHttpResponseMessage
    {
        return $this->createResponseWithBody((string)$foo);
    }

    /**
     * Gets the current request (for use in tests)
     *
     * @return IHttpRequestMessage The current request
     */
    public function getRequest(): IHttpRequestMessage
    {
        return $this->request;
    }

    /**
     * Mocks a method with an int parameter
     *
     * @param int $foo The int
     * @return IHttpResponseMessage The response
     */
    public function intParameter(int $foo): IHttpResponseMessage
    {
        return $this->createResponseWithBody((string)$foo);
    }

    /**
     * Mocks a method that takes in no parameters
     *
     * @return Response The method name
     */
    public function noParameters(): IHttpResponseMessage
    {
        return $this->createResponseWithBody('noParameters');
    }

    /**
     * Mocks a method with a parameter with no type hint
     *
     * @param mixed $foo The parameter to use in the response
     * @return Response The response
     */
    public function noTypeHintParameter($foo): IHttpResponseMessage
    {
        return $this->createResponseWithBody((string)$foo);
    }

    /**
     * Mocks a method that takes in a nullable object parameter
     *
     * @param User|null $user The user
     * @return Response The response
     */
    public function nullableObjectParameter(?User $user): IHttpResponseMessage
    {
        return $this->createResponseWithBody($user === null ? 'null' : 'notnull');
    }

    /**
     * Mocks a method that takes in a nullable scalar parameter
     *
     * @param int|null $foo The nullable parameter
     * @return Response The response
     */
    public function nullableScalarParameter(?int $foo): IHttpResponseMessage
    {
        return $this->createResponseWithBody($foo === null ? 'null' : 'notnull');
    }

    /**
     * Mocks a method with an object parameter
     *
     * @param User $user The user
     * @return IHttpResponseMessage The response
     */
    public function objectParameter(User $user): IHttpResponseMessage
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
     * @return IHttpResponseMessage The response
     */
    public function stringParameter(string $foo): IHttpResponseMessage
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
    protected function protectedMethod(): IHttpResponseMessage
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
        return new Response(HttpStatusCodes::HTTP_OK, null, new StringBody($body));
    }

    /**
     * Mocks a private method for use in testing
     *
     * @return Response The name of the method
     */
    private function privateMethod(): IHttpResponseMessage
    {
        return $this->createResponseWithBody('privateMethod');
    }
}
