<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http;

use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpStatusCode;
use Aphiria\Net\Http\IResponse;
use Exception;
use PHPUnit\Framework\TestCase;

class HttpExceptionTest extends TestCase
{
    public function testCodeIsSameOneSetInConstructor(): void
    {
        $exception = new HttpException(500, '', 4);
        $this->assertSame(4, $exception->getCode());
    }

    public function testEnumStatusCodeIsSetInResponse(): void
    {
        $exception = new HttpException(HttpStatusCode::InternalServerError);
        $this->assertSame(HttpStatusCode::InternalServerError, $exception->response->getStatusCode());
    }

    public function testIntStatusCodeIsSetInResponse(): void
    {
        $exception = new HttpException(500);
        $this->assertSame(HttpStatusCode::InternalServerError, $exception->response->getStatusCode());
    }

    public function testMessageIsSameOneSetInConstructor(): void
    {
        $exception = new HttpException(500, 'foo');
        $this->assertSame('foo', $exception->getMessage());
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
        $this->assertSame($response, $exception->response);
    }
}
