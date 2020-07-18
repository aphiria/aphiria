<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http;

use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\IResponse;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class HttpExceptionTest extends TestCase
{
    public function testCodeIsSameOneSetInConstructor(): void
    {
        $exception = new HttpException(500, '', 4);
        $this->assertEquals(4, $exception->getCode());
    }

    public function testInvalidStatusCodeOrResponseThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('First parameter must be either a status code or an HTTP response');
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
        $response = $this->createMock(IResponse::class);
        $exception = new HttpException($response);
        $this->assertSame($response, $exception->getResponse());
    }
}
