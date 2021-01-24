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

use Aphiria\Net\Http\HttpStatusCodes;
use PHPUnit\Framework\TestCase;

class HttpStatusCodesTest extends TestCase
{
    public function testExistingStatusCodeReturnsDefaultStatusText(): void
    {
        $this->assertSame('OK', HttpStatusCodes::getDefaultReasonPhrase(200));
    }

    public function testNonExistentStatusCodeReturnsNullStatusText(): void
    {
        $this->assertNull(HttpStatusCodes::getDefaultReasonPhrase(-1));
    }
}
