<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Responses;

use DateTime;
use InvalidArgumentException;

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
        $this->cookie = new Cookie('name', 'value', 1234, '/', 'foo.com', true, true);
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
     * Tests getting the value
     */
    public function testGettingValue() : void
    {
        $this->assertEquals('value', $this->cookie->getValue());
    }

    /**
     * Tests setting a DateTime as the expiration uses that DateTime
     */
    public function testSettingDateTimeAsExpirationUsesThatDateTime() : void
    {
        $expiration = new DateTime();
        $cookie = new Cookie('name', 'value', $expiration);
        $this->assertSame($expiration, $cookie->getExpiration());
    }

    /**
     * Tests setting an invalid expiration value throws an exception
     */
    public function testSettingInvalidExpirationValueThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->cookie->setExpiration('foo');
    }
}
