<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http\Headers;

use Aphiria\Net\Http\Headers\Cookie;
use Aphiria\Net\Http\Headers\SameSiteMode;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CookieTest extends TestCase
{
    private Cookie $cookie;

    protected function setUp(): void
    {
        $this->cookie = new Cookie('name', 'value', 1234, '/', 'foo.com', true, true, SameSiteMode::Lax);
    }

    public function testGettingName(): void
    {
        $this->assertSame('name', $this->cookie->getName());
    }

    public function testInvalidNameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cookie name "=" contains invalid characters');
        $this->cookie->setName('=');
    }
}
