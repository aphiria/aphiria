<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Tests\Dispatchers\Mocks;

use Opulence\Api\ApiController as BaseApiController;
use Opulence\Net\Http\HttpStatusCodes;
use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Net\Http\Response;
use Opulence\Net\Http\StringBody;
use RuntimeException;

/**
 * Defines a mock controller for use in testing
 */
class ApiController extends BaseApiController
{
    /**
     * Mocks a method that takes in multiple parameters with some default values
     *
     * @param mixed $foo The first parameter
     * @param mixed $bar The second parameter
     * @param mixed $blah The optional third parameter
     * @return IHttpResponseMessage The parameter names to their values
     */
    public function multipleParametersWithDefaultValues($foo, $bar, $blah = '724'): IHttpResponseMessage
    {
        return $this->createResponseWithBody("foo:$foo, bar:$bar, blah:$blah");
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
     * Mocks a method that takes in a single parameter
     *
     * @param mixed $foo The parameter
     * @return Response The parameter name to its value
     */
    public function oneParameter($foo): IHttpResponseMessage
    {
        return $this->createResponseWithBody("foo:$foo");
    }

    /**
     * Mocks a method that does not return anything
     */
    public function returnsNothing(): void
    {
        // Don't do anything
    }

    /**
     * Mocks a method that takes in several parameters
     *
     * @param mixed $foo The first parameter
     * @param mixed $bar The second parameter
     * @param mixed $baz The third parameter
     * @param mixed $blah The fourth parameter
     * @return Response The parameter names to their values
     */
    public function severalParameters($foo, $bar, $baz, $blah): IHttpResponseMessage
    {
        return $this->createResponseWithBody("foo:$foo, bar:$bar, baz:$baz, blah:$blah");
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
     * Mocks a method that takes in two parameters
     *
     * @param mixed $foo The first parameter
     * @param mixed $bar The second parameter
     * @return Response The parameter names to their values
     */
    public function twoParameters($foo, $bar): IHttpResponseMessage
    {
        return $this->createResponseWithBody("foo:$foo, bar:$bar");
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
