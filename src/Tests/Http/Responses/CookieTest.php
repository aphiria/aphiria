<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Responses;

use DateTime;
use InvalidArgumentException;
use Opulence\Net\Http\Responses\Cookie;

/**
 * Tests cookies
 */
class CookieTest extends \PHPUnit\Framework\TestCase
{
    /** @var Cookie The cookie to use in tests */
    private $cookie = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->cookie = new Cookie('name', 'value', 1234, '/', 'foo.com', true, true, Cookie::SAME_SITE_LAX);
    }

    /**
     * Tests checking if the is-HTTP-only flag returns the correct value
     */
    public function testCheckingIfIsHttpOnly() : void
    {
        $this->assertTrue($this->cookie->isHttpOnly());
    }

    /**
     * Tests checking if the is-secure flag returns the correct value
     */
    public function testCheckingIfIsSecure() : void
    {
        $this->assertTrue($this->cookie->isSecure());
    }

    /**
     * Tests getting the domain
     */
    public function testGettingDomain() : void
    {
        $this->assertEquals('foo.com', $this->cookie->getDomain());
    }

    /**
     * Tests getting the expiration
     */
    public function testGettingExpiration() : void
    {
        $this->assertEquals(1234, (int)$this->cookie->getExpiration()->format('U'));
    }

    /**
     * Tests that the max age is set from the expiration
     */
    public function testGettingMaxAge() : void
    {
        $this->assertEquals(1234, $this->cookie->getMaxAge());
    }

    /**
     * Tests getting the name
     */
    public function testGettingName() : void
    {
        $this->assertEquals('name', $this->cookie->getName());
    }

    /**
     * Tests getting the path
     */
    public function testGettingPath() : void
    {
        $this->assertEquals('/', $this->cookie->getPath());
    }

    /**
     * Tests getting the same-site value
     */
    public function testGettingSameSite() : void
    {
        $this->assertEquals(Cookie::SAME_SITE_LAX, $this->cookie->getSameSite());
    }

    /**
     * Tests getting the value
     */
    public function testGettingValue() : void
    {
        $this->assertEquals('value', $this->cookie->getValue());
    }

    /**
     * Tests that an invalid name throws an exception
     */
    public function testInvalidNameThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->cookie->setName('=');
    }

    /**
     * Tests setting a DateTime as the expiration uses that DateTime and doesn't set the max age
     */
    public function testSettingDateTimeAsExpirationDoesNotSetMaxAge() : void
    {
        $expiration = new DateTime();
        $cookie = new Cookie('name', 'value', $expiration);
        $this->assertSame($expiration, $cookie->getExpiration());
        $this->assertNull($cookie->getMaxAge());
    }

    /**
     * Tests setting an invalid expiration value throws an exception
     */
    public function testSettingInvalidExpirationValueThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new Cookie('foo', 'bar', 'baz');
    }

    /**
     * Tests setting the max age
     */
    public function testSettingMaxAge(): void
    {
        $this->cookie->setMaxAge(3600);
        $this->assertEquals(3600, $this->cookie->getMaxAge());
    }

    /**
     * Tests that setting a null expiration sets a null expiration
     */
    public function testSettingNullExpirationSetsNullExpiration() : void
    {
        $this->cookie->setExpiration(null);
        $this->assertNull($this->cookie->getExpiration());
    }
}
