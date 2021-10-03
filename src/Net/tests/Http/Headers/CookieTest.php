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
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CookieTest extends TestCase
{
    private Cookie $cookie;

    protected function setUp(): void
    {
        $this->cookie = new Cookie('name', 'value', 1234, '/', 'foo.com', true, true, Cookie::SAME_SITE_LAX);
    }

    public function getSameSiteSettings(): array
    {
        return [
            [Cookie::SAME_SITE_STRICT],
            [Cookie::SAME_SITE_LAX],
            [Cookie::SAME_SITE_NONE],
            [null]
        ];
    }

    public function testInvalidSameSiteThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Acceptable values for SameSite are "lax", "strict", "none", or null');
        new Cookie('foo', 'bar', null, null, null, false, false, 'foo');
    }

    public function testGettingName(): void
    {
        $this->assertSame('name', $this->cookie->getName());
    }

    public function testGettingSameSite(): void
    {
        $this->assertSame(Cookie::SAME_SITE_LAX, $this->cookie->getSameSite());
    }

    public function testInvalidNameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cookie name "=" contains invalid characters');
        $this->cookie->setName('=');
    }

    public function testSettingSameSite(): void
    {
        $cookie = new Cookie('foo', 'bar', sameSite: null);
        $this->assertNull($cookie->getSameSite());
        $cookie->setSameSite(Cookie::SAME_SITE_LAX);
        $this->assertSame(Cookie::SAME_SITE_LAX, $cookie->getSameSite());
    }

    /**
     * @dataProvider getSameSiteSettings
     * @param string|null $sameSite The same site setting to test
     */
    public function testSettingValidSameSiteSettingsAreAccepted(?string $sameSite): void
    {
        $cookie = new Cookie('foo', 'bar', sameSite: $sameSite);
        // Also try setting the value manually
        $cookie->setSameSite($sameSite);
        // Dummy assertion
        $this->assertTrue(true);
    }
}
