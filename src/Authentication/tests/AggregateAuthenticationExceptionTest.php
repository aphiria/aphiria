<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication\Tests;

use Aphiria\Authentication\AggregateAuthenticationException;
use Exception;
use PHPUnit\Framework\TestCase;

class AggregateAuthenticationExceptionTest extends TestCase
{
    public function testEmptyInnerExceptionsAreAccepted(): void
    {
        $aggregateException = new AggregateAuthenticationException('foo', []);
        $this->assertEmpty($aggregateException->innerExceptions);
    }

    public function testMessageSet(): void
    {
        $aggregateException = new AggregateAuthenticationException('foo', new Exception());
        $this->assertSame('foo', $aggregateException->getMessage());
    }

    public function testMultipleExceptionIsConvertedToList(): void
    {
        $innerExceptions = [new Exception(), new Exception()];
        $aggregateException = new AggregateAuthenticationException('foo', $innerExceptions);
        $this->assertSame($innerExceptions, $aggregateException->innerExceptions);
    }

    public function testSingleExceptionIsConvertedToList(): void
    {
        $innerException = new Exception();
        $aggregateException = new AggregateAuthenticationException('foo', $innerException);
        $this->assertSame([$innerException], $aggregateException->innerExceptions);
    }
}
