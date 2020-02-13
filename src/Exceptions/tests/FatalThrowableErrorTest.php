<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests\Exceptions;

use Aphiria\Exceptions\FatalThrowableError;
use ErrorException;
use InvalidArgumentException;
use ParseError;
use PHPUnit\Framework\TestCase;
use TypeError;

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
