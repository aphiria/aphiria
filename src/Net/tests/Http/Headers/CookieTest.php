<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
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

    public function testCheckingIfIsHttpOnly(): void
    {
        $this->assertTrue($this->cookie->isHttpOnly());
    }

    public function testInvalidSameSiteThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Acceptable values for SameSite are "lax", "strict", "none", or null');
        new Cookie('foo', 'bar', null, null, null, false, false, 'foo');
    }

    public function testSetHttpOnly(): void
    {
        $this->cookie->setHttpOnly(false);
        $this->assertFalse($this->cookie->isHttpOnly());
    }

    public function testSetDomain(): void
    {
        $domainName = 'www.domain.com';
        $this->cookie->setDomain($domainName);
        $this->assertSame($domainName, $this->cookie->getDomain());
    }

    public function testSetPath(): void
    {
        $path = '/';
        $this->cookie->setPath($path);
        $this->assertSame($path, $this->cookie->getPath());
    }

    public function setValueProvider(): array
    {
        return [
            ['123'],
            [12345],
            [
                [12345],
            ],
        ];
    }

    /**
     * @dataProvider setValueProvider
     */
    public function testSetValue($value): void
    {
        $this->cookie->setValue($value);
        $this->assertEquals($value, $this->cookie->getValue());
    }

    public function testSetSecure(): void
    {
        $isSecure = false;
        $this->cookie->setSecure($isSecure);
        $this->assertFalse($this->cookie->isSecure());
    }

    public function testCheckingIfIsSecure(): void
    {
        $this->assertTrue($this->cookie->isSecure());
    }

    public function testGettingDomain(): void
    {
        $this->assertSame('foo.com', $this->cookie->getDomain());
    }

    public function testGettingMaxAge(): void
    {
        $this->assertSame(1234, $this->cookie->getMaxAge());
    }

    public function testGettingName(): void
    {
        $this->assertSame('name', $this->cookie->getName());
    }

    public function testGettingPath(): void
    {
        $this->assertSame('/', $this->cookie->getPath());
    }

    public function testGettingSameSite(): void
    {
        $this->assertSame(Cookie::SAME_SITE_LAX, $this->cookie->getSameSite());
    }

    public function testGettingValue(): void
    {
        $this->assertSame('value', $this->cookie->getValue());
    }

    public function testInvalidNameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cookie name "=" contains invalid characters');
        $this->cookie->setName('=');
    }

    public function testSettingMaxAge(): void
    {
        $this->cookie->setMaxAge(3600);
        $this->assertSame(3600, $this->cookie->getMaxAge());
    }

    /**
     * @dataProvider getSameSiteSettings
     * @param string|null $sameSite The same site setting to test
     */
    public function testSettingValidSameSiteSettingsAreAccepted(?string $sameSite): void
    {
        new Cookie('foo', 'bar', sameSite: $sameSite);
        // Dummy assertion
        $this->assertTrue(true);
    }
}
