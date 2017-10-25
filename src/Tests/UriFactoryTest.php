<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests;

use InvalidArgumentException;
use Opulence\Net\UriFactory;

/**
 * Tests the URI factory
 */
class UriFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var UriFactory The URI factory to use in tests */
    private $factory = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->factory = new UriFactory();
    }

    /**
     * Tests creating from a string creates a URI with the correct values
     */
    public function testCreatingFromStringCreatesUriWithCorrectValues() : void
    {
        $uri = $this->factory->createUriFromString('https://user:password@host:8080/path?query#fragment');
        $this->assertEquals('https', $uri->getScheme());
        $this->assertEquals('user', $uri->getUser());
        $this->assertEquals('password', $uri->getPassword());
        $this->assertEquals('host', $uri->getHost());
        $this->assertEquals(8080, $uri->getPort());
        $this->assertEquals('/path', $uri->getPath());
        $this->assertEquals('query', $uri->getQueryString());
        $this->assertEquals('fragment', $uri->getFragment());
    }

    /**
     * Tests a malformed URI throws an exception when creating from string
     */
    public function testMalformedUriThrowsExceptionWhenCreatingFromString() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->factory->createUriFromString('host:65536');
    }
}
