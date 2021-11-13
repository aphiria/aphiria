<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http;

use Aphiria\Net\Http\HttpStatusCode;
use PHPUnit\Framework\TestCase;

class HttpStatusCodesTest extends TestCase
{
    public function testExistingStatusCodeAsEnumReturnsDefaultStatusText(): void
    {
        $this->assertSame('OK', HttpStatusCode::getDefaultReasonPhrase(HttpStatusCode::Ok));
    }

    public function testExistingStatusCodeAsIntReturnsDefaultStatusText(): void
    {
        $this->assertSame('OK', HttpStatusCode::getDefaultReasonPhrase(200));
    }

    public function testNonExistentStatusCodeReturnsNullStatusText(): void
    {
        $this->assertNull(HttpStatusCode::getDefaultReasonPhrase(-1));
    }
}
