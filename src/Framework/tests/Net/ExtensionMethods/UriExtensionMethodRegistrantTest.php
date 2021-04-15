<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Net\ExtensionMethods;

use Aphiria\ExtensionMethods\ExtensionMethodRegistry;
use Aphiria\Framework\Net\ExtensionMethods\UriExtensionMethodRegistrant;
use Aphiria\Net\Uri;
use Closure;
use PHPUnit\Framework\TestCase;

class UriExtensionMethodRegistrantTest extends TestCase
{
    private UriExtensionMethodRegistrant $registrant;

    protected function setUp(): void
    {
        ExtensionMethodRegistry::reset();
        $this->registrant = new UriExtensionMethodRegistrant();
    }

    public function provideExtensionMethods(): array
    {
        return [
            ['parseQueryString']
        ];
    }

    /**
     * @dataProvider provideExtensionMethods
     * @param string $methodName The name of the extension method
     */
    public function testRegisterRegistersExtensionMethods(string $methodName): void
    {
        $uri = new Uri('http://localhost');
        $this->assertNull(ExtensionMethodRegistry::getExtensionMethod($uri, $methodName));
        // The previous call would've memoized a null closure, so reset it
        ExtensionMethodRegistry::reset();
        $this->registrant->registerExtensionMethods();
        $this->assertInstanceOf(Closure::class, ExtensionMethodRegistry::getExtensionMethod($uri, $methodName));
    }
}
