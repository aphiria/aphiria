<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Tests\Exceptions;

use TypeError;
use ParseError;
use ErrorException;
use InvalidArgumentException;
use Opulence\Api\Exceptions\FatalThrowableError;
use PHPUnit\Framework\TestCase;

class FatalThrowableErrorTest extends TestCase
{
    public function throwableProvider(): array
    {
        return [
            [new ParseError],
            [new TypeError],
            [new InvalidArgumentException],
        ];
    }

    /**
     * @dataProvider throwableProvider
     */
    public function testConstructor($throwable): void
    {
        $throwableError = new FatalThrowableError($throwable);
        $this->assertInstanceOf(ErrorException::class, $throwableError);
    }
}
