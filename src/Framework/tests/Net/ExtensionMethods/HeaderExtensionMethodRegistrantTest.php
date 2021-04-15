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
use Aphiria\Framework\Net\ExtensionMethods\HeaderExtensionMethodRegistrant;
use Aphiria\Net\Http\Headers;
use Closure;
use PHPUnit\Framework\TestCase;

class HeaderExtensionMethodRegistrantTest extends TestCase
{
    private HeaderExtensionMethodRegistrant $registrant;

    protected function setUp(): void
    {
        ExtensionMethodRegistry::reset();
        $this->registrant = new HeaderExtensionMethodRegistrant();
    }

    public function provideExtensionMethods(): array
    {
        return [
            ['isJson'],
            ['isMultipart'],
            ['parseContentTypeHeader'],
            ['parseParameters']
        ];
    }

    /**
     * @dataProvider provideExtensionMethods
     * @param string $methodName The name of the extension method
     */
    public function testRegisterRegistersExtensionMethods(string $methodName): void
    {
        $headers = new Headers();
        $this->assertNull(ExtensionMethodRegistry::getExtensionMethod($headers, $methodName));
        // The previous call would've memoized a null closure, so reset it
        ExtensionMethodRegistry::reset();
        $this->registrant->registerExtensionMethods();
        $this->assertInstanceOf(Closure::class, ExtensionMethodRegistry::getExtensionMethod($headers, $methodName));
    }
}
