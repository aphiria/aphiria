<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests;

use Opulence\Routing\UriTemplates\UriTemplate;

/**
 * Tests the URI template
 */
class UriTemplateTest extends \PHPUnit\Framework\TestCase
{
    public function testHostIsNullIfNoValueIsSpecified(): void
    {
        $uriTemplate = new UriTemplate('/foo');
        $this->assertNull($uriTemplate->hostTemplate);
    }

    public function testIsAbsoluteUriDependsOnHostTemplateBeingNull(): void
    {
        $uriTemplate = new UriTemplate('/foo');
        $this->assertFalse($uriTemplate->isAbsoluteUri);
        $uriTemplate = new UriTemplate('/foo', 'example.com');
        $this->assertTrue($uriTemplate->isAbsoluteUri);
    }

    public function testLeadingSlashIsAddedToPath(): void
    {
        $uriTemplate = new UriTemplate('foo');
        $this->assertEquals('/foo', $uriTemplate->pathTemplate);
        $uriTemplate = new UriTemplate('/foo');
        $this->assertEquals('/foo', $uriTemplate->pathTemplate);
    }

    public function testPropertiesAreSetInConstructor(): void
    {
        $uriTemplate = new UriTemplate('/foo', 'example.com', false);
        $this->assertEquals('/foo', $uriTemplate->pathTemplate);
        $this->assertEquals('example.com', $uriTemplate->hostTemplate);
        $this->assertFalse($uriTemplate->isHttpsOnly);
        $this->assertTrue($uriTemplate->isAbsoluteUri);
    }

    public function testToStringIgnoresHostIfItIsNull(): void
    {
        $uriTemplate = new UriTemplate('/foo');
        $this->assertEquals('/foo', (string)$uriTemplate);
    }

    public function testToStringIncludesHostIfItIsDefined(): void
    {
        $uriTemplate = new UriTemplate('/foo', 'example.com');
        $this->assertEquals('example.com/foo', (string)$uriTemplate);
    }

    public function testTrailingSlashIsStrippedFromHost(): void
    {
        $uriTemplate = new UriTemplate('foo', 'example.com/');
        $this->assertEquals('example.com', $uriTemplate->hostTemplate);
    }
}
