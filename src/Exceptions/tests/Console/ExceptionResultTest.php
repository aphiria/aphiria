<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Exceptions\Tests\Console;

use Aphiria\Exceptions\Console\ExceptionResult;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the exception results
 */
class ExceptionResultTest extends TestCase
{
    public function testArrayOfMessagesAreAccepted(): void
    {
        $result = new ExceptionResult(0, ['foo', 'bar']);
        $this->assertEquals(['foo', 'bar'], $result->getMessages());
    }

    public function testGetStatusCodeReturnsOneSetInConstructor(): void
    {
        $result = new ExceptionResult(1, 'foo');
        $this->assertEquals(1, $result->getStatusCode());
    }

    public function testNonStringMessageThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Messages must be a string or array of strings');
        new ExceptionResult(0, $this);
    }

    public function testOutOfBoundsStatusCodesAreMovedInBounds(): void
    {
        $lowStatusCodeResult = new ExceptionResult(-1, 'foo');
        $highStatusCodeResult = new ExceptionResult(255, 'bar');
        $this->assertEquals(0, $lowStatusCodeResult->getStatusCode());
        $this->assertEquals(254, $highStatusCodeResult->getStatusCode());
    }

    public function testStringMessageGetsConvertedToArrayOfStrings(): void
    {
        $result = new ExceptionResult(0, 'foo');
        $this->assertEquals(['foo'], $result->getMessages());
    }
}
