<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests;

use Exception;
use InvalidArgumentException;
use Opulence\Net\Uri;

/**
 * Tests the URI
 */
class UriTest extends \PHPUnit\Framework\TestCase
{
    /** @var Uri The URI to use in tests */
    private $uri = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->uri = new Uri(
            'http',
            'user',
            'password',
            'host',
            80,
            'path',
            'query',
            'fragment'
        );
    }

    /**
     * Tests getting the fragment
     */
    public function testGettingFragment() : void
    {
        $this->assertEquals('fragment', $this->uri->getFragment());
    }

    /**
     * Tests getting the host
     */
    public function testGettingHost() : void
    {
        $this->assertEquals('host', $this->uri->getHost());
    }

    /**
     * Tests getting the password
     */
    public function testGettingPassword() : void
    {
        $this->assertEquals('password', $this->uri->getPassword());
    }

    /**
     * Tests getting the path
     */
    public function testGettingPath() : void
    {
        $this->assertEquals('path', $this->uri->getPath());
    }

    /**
     * Tests getting the port
     */
    public function testGettingPort() : void
    {
        $this->assertEquals(80, $this->uri->getPort());
    }

    /**
     * Tests getting the query string
     */
    public function testGettingQueryString() : void
    {
        $this->assertEquals('query', $this->uri->getQueryString());
    }

    /**
     * Tests getting the scheme
     */
    public function testGettingScheme() : void
    {
        $this->assertEquals('http', $this->uri->getScheme());
    }

    /**
     * Tests getting the user
     */
    public function testGettingUser() : void
    {
        $this->assertEquals('user', $this->uri->getUser());
    }

    /**
     * Tests that an out of range port throws an exception
     */
    public function testOutOfRangePortThrowsException() : void
    {
        try {
            new Uri(
                'http',
                'user',
                'password',
                'host',
                0,
                'path',
                'query',
                'fragment'
            );
            $this->fail('Port below acceptable range was accepted');
        } catch (InvalidArgumentException $ex) {
            // Verify we got here
            $this->assertTrue(true);
        } catch (Exception $ex) {
            // Don't want to get here
            $this->assertTrue(false);
        }

        try {
            new Uri(
                'http',
                'user',
                'password',
                'host',
                65536,
                'path',
                'query',
                'fragment'
            );
            $this->fail('Port above acceptable range was accepted');
        } catch (InvalidArgumentException $ex) {
            // Verify we got here
            $this->assertTrue(true);
        } catch (Exception $ex) {
            // Don't want to get here
            $this->assertTrue(false);
        }
    }

    /**
     * Tests casting to string with all parts is created correctly
     */
    public function testToStringWithAllPartsIsCreatedCorrectly() : void
    {
        $uri = new Uri(
            'http',
            'user',
            'password',
            'host',
            8080,
            '/path',
            'query',
            'fragment'
        );
        $this->assertEquals('http://user:password@host:8080/path?query#fragment', (string)$uri);
    }

    /**
     * Tests casting to string with fragment includes the fragment
     */
    public function testToStringWithFragmentStringIncludesFragment() : void
    {
        $uri = new Uri(
            'http',
            null,
            null,
            'host',
            80,
            '',
            null,
            'fragment'
        );
        $this->assertEquals('http://host#fragment', (string)$uri);
    }

    /**
     * Tests casting to string with a non-standard port includes the port
     */
    public function testToStringWithNonStandardPortIncludesPort() : void
    {
        $httpUri = new Uri(
            'http',
            'user',
            'password',
            'host',
            8080,
            '',
            null,
            null
        );
        $this->assertEquals('http://user:password@host:8080', (string)$httpUri);
        $httpsUri = new Uri(
            'https',
            'user',
            'password',
            'host',
            1234,
            '',
            null,
            null
        );
        $this->assertEquals('https://user:password@host:1234', (string)$httpsUri);
    }

    /**
     * Tests casting to string with no user or password does not include those value
     */
    public function testToStringWithNoUserPasswordDoesNotIncludeThoseValues() : void
    {
        $uri = new Uri(
            'http',
            null,
            null,
            'host',
            null,
            '',
            null,
            null
        );
        $this->assertEquals('http://host', (string)$uri);
    }

    /**
     * Tests casting to string with query string includes the query string
     */
    public function testToStringWithQueryStringIncludesQueryString() : void
    {
        $uri = new Uri(
            'http',
            null,
            null,
            'host',
            80,
            '',
            'query',
            null
        );
        $this->assertEquals('http://host?query', (string)$uri);
    }

    /**
     * Tests casting to string with user or password incldues those value
     */
    public function testToStringWithUserPasswordIncludesThoseValues() : void
    {
        $uri = new Uri(
            'http',
            'user',
            'password',
            'host',
            null,
            '',
            null,
            null
        );
        $this->assertEquals('http://user:password@host', (string)$uri);
    }
}
