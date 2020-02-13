<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests;

use Aphiria\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\TestCase;

/**
 * Tests the URI template
 */
class UriTemplateTest extends TestCase
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

    public function leadingSlashUriProvider(): array
    {
        return [
            ['foo', '/foo'],
            ['/foo', '/foo'],
        ];
    }

    /**
     * @dataProvider leadingSlashUriProvider
     */
    public function testLeadingSlashIsAddedToPath($uri, $expectedUri): void
    {
        $uriTemplate = new UriTemplate($uri);
        $this->assertEquals($expectedUri, $uriTemplate->pathTemplate);
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
