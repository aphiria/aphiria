<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http;

use Exception;
use InvalidArgumentException;
use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\IHttpResponseMessage;

/**
 * Tests the HTTP exception
 */
class HttpExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testCodeIsSameOneSetInConstructor(): void
    {
        $exception = new HttpException(500, '', 4);
        $this->assertEquals(4, $exception->getCode());
    }

    public function testInvalidStatusCodeOrResponseThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new HttpException('foo');
    }

    public function testIntStatusCodeIsSetInResponse(): void
    {
        $exception = new HttpException(500);
        $this->assertEquals(500, $exception->getResponse()->getStatusCode());
    }

    public function testMessageIsSameOneSetInConstructor(): void
    {
        $exception = new HttpException(500, 'foo');
        $this->assertEquals('foo', $exception->getMessage());
    }

    public function testPreviousExceptionIsSameOneSetInConstructor(): void
    {
        $previousException = new Exception();
        $exception = new HttpException(500, '', 0, $previousException);
        $this->assertSame($previousException, $exception->getPrevious());
    }

    public function testResponseSetInConstructorIsUsedAsResponseInException(): void
    {
        $response = $this->createMock(IHttpResponseMessage::class);
        $exception = new HttpException($response);
        $this->assertSame($response, $exception->getResponse());
    }
}
