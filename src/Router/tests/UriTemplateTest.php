<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests;

use Aphiria\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\TestCase;

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
     * @param string $pathTemplate The actual path template
     * @param string $expectedPathTemplate The expected path template
     */
    public function testLeadingSlashIsAddedToPath(string $pathTemplate, string $expectedPathTemplate): void
    {
        $uriTemplate = new UriTemplate($pathTemplate);
        $this->assertEquals($expectedPathTemplate, $uriTemplate->pathTemplate);
    }

    public function testPropertiesAreSetInConstructor(): void
    {
        $uriTemplate = new UriTemplate('/foo', 'example.com', false);
        $this->assertSame('/foo', $uriTemplate->pathTemplate);
        $this->assertSame('example.com', $uriTemplate->hostTemplate);
        $this->assertFalse($uriTemplate->isHttpsOnly);
        $this->assertTrue($uriTemplate->isAbsoluteUri);
    }

    public function testToStringIgnoresHostIfItIsNull(): void
    {
        $uriTemplate = new UriTemplate('/foo');
        $this->assertSame('/foo', (string)$uriTemplate);
    }

    public function testToStringIncludesHostIfItIsDefined(): void
    {
        $uriTemplate = new UriTemplate('/foo', 'example.com');
        $this->assertSame('example.com/foo', (string)$uriTemplate);
    }

    public function testTrailingSlashIsStrippedFromHost(): void
    {
        $uriTemplate = new UriTemplate('foo', 'example.com/');
        $this->assertSame('example.com', $uriTemplate->hostTemplate);
    }
}
