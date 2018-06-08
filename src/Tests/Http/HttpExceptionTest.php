<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http;

use Exception;
use Opulence\Collections\KeyValuePair;
use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\HttpHeaders;

/**
 * Tests the HTTP exception
 */
class HttpExceptionTest extends \PHPUnit\Framework\TestCase
{
    /** @var HttpException The exception to use in tests */
    private $exception;
    /** @var Exception The previous exception */
    private $previousException;

    public function setUp()
    {
        $this->previousException = new Exception();
        $this->exception = new HttpException(
            404,
            'foo',
            new HttpHeaders([new KeyValuePair('bar', 'baz')]),
            4,
            $this->previousException
        );
    }

    public function testGettingCode(): void
    {
        $this->assertEquals(4, $this->exception->getCode());
    }

    public function testGettingHeaders(): void
    {
        $this->assertEquals('baz', $this->exception->getHeaders()->getFirst('bar'));
    }

    public function testGettingMessage(): void
    {
        $this->assertEquals('foo', $this->exception->getMessage());
    }

    public function testGettingPreviousException(): void
    {
        $this->assertSame($this->previousException, $this->exception->getPrevious());
    }

    public function testGettingStatusCode(): void
    {
        $this->assertEquals(404, $this->exception->getStatusCode());
    }
}
