<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Exceptions\Tests;

use Aphiria\Exceptions\FatalThrowableError;
use ErrorException;
use InvalidArgumentException;
use ParseError;
use PHPUnit\Framework\TestCase;
use Throwable;
use TypeError;

class FatalThrowableErrorTest extends TestCase
{
    public function throwableProvider(): array
    {
        return [
            [new ParseError()],
            [new TypeError()],
            [new InvalidArgumentException()],
        ];
    }

    /**
     * @dataProvider throwableProvider
     * @param Throwable $throwable The throwable error
     */
    public function testConstructor(Throwable $throwable): void
    {
        $throwableError = new FatalThrowableError($throwable);
        $this->assertInstanceOf(ErrorException::class, $throwableError);
    }
}
