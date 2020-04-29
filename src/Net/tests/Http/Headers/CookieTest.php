<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
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
        $this->assertEquals($domainName, $this->cookie->getDomain());
    }

    public function testSetPath(): void
    {
        $path = '/';
        $this->cookie->setPath($path);
        $this->assertEquals($path, $this->cookie->getPath());
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
        $this->assertEquals('foo.com', $this->cookie->getDomain());
    }

    public function testGettingMaxAge(): void
    {
        $this->assertEquals(1234, $this->cookie->getMaxAge());
    }

    public function testGettingName(): void
    {
        $this->assertEquals('name', $this->cookie->getName());
    }

    public function testGettingPath(): void
    {
        $this->assertEquals('/', $this->cookie->getPath());
    }

    public function testGettingSameSite(): void
    {
        $this->assertEquals(Cookie::SAME_SITE_LAX, $this->cookie->getSameSite());
    }

    public function testGettingValue(): void
    {
        $this->assertEquals('value', $this->cookie->getValue());
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
        $this->assertEquals(3600, $this->cookie->getMaxAge());
    }
}
