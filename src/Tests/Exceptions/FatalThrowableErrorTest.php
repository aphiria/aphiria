<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/api/blob/master/LICENSE.md
 */

namespace Aphiria\Api\Tests\Exceptions;

use TypeError;
use ParseError;
use ErrorException;
use InvalidArgumentException;
use Aphiria\Api\Exceptions\FatalThrowableError;
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
