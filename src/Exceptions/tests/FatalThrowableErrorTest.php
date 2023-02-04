<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Exceptions\Tests;

use Aphiria\Exceptions\FatalThrowableError;
use ErrorException;
use InvalidArgumentException;
use ParseError;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Throwable;
use TypeError;

class FatalThrowableErrorTest extends TestCase
{
    /**
     * @param Throwable $throwable The throwable error
     */
    #[TestWith([new ParseError()])]
    #[TestWith([new TypeError()])]
    #[TestWith([new InvalidArgumentException()])]
    public function testConstructor(Throwable $throwable): void
    {
        $throwableError = new FatalThrowableError($throwable);
        $this->assertInstanceOf(ErrorException::class, $throwableError);
    }
}
