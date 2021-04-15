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
use Aphiria\Framework\Net\ExtensionMethods\RequestExtensionMethodRegistrant;
use Aphiria\Net\Http\IRequest;
use Closure;
use PHPUnit\Framework\TestCase;

class RequestExtensionMethodRegistrantTest extends TestCase
{
    private RequestExtensionMethodRegistrant $registrant;

    protected function setUp(): void
    {
        ExtensionMethodRegistry::reset();
        $this->registrant = new RequestExtensionMethodRegistrant();
    }

    public function provideExtensionMethods(): array
    {
        return [
            ['getActualMimeType'],
            ['getClientIPAddress'],
            ['isJson'],
            ['isMultipart'],
            ['parseAcceptCharsetHeader'],
            ['parseAcceptHeader'],
            ['parseAcceptLanguageHeader'],
            ['parseContentTypeHeader'],
            ['parseCookies'],
            ['parseParameters'],
            ['parseQueryString'],
            ['readAsFormInput'],
            ['readAsJson'],
            ['readAsMultipart']
        ];
    }

    /**
     * @dataProvider provideExtensionMethods
     * @param string $methodName The name of the extension method
     */
    public function testRegisterRegistersExtensionMethods(string $methodName): void
    {
        $request = $this->createMock(IRequest::class);
        $this->assertNull(ExtensionMethodRegistry::getExtensionMethod($request, $methodName));
        // The previous call would've memoized a null closure, so reset it
        ExtensionMethodRegistry::reset();
        $this->registrant->registerExtensionMethods();
        $this->assertInstanceOf(Closure::class, ExtensionMethodRegistry::getExtensionMethod($request, $methodName));
    }
}
