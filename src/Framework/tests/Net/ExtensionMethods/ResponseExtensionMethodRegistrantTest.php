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
use Aphiria\Framework\Net\ExtensionMethods\ResponseExtensionMethodRegistrant;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\Response;
use Closure;
use PHPUnit\Framework\TestCase;

class ResponseExtensionMethodRegistrantTest extends TestCase
{
    private ResponseExtensionMethodRegistrant $registrant;

    protected function setUp(): void
    {
        ExtensionMethodRegistry::reset();
        $this->registrant = new ResponseExtensionMethodRegistrant();
    }

    public function provideExtensionMethods(): array
    {
        return [
            ['deleteCookie'],
            ['setCookie'],
            ['setCookies'],
            ['redirectToUri'],
            ['writeJson']
        ];
    }

    public function testDeleteCookieActuallyRemovesCookie(): void
    {
        $response = new Response();
        $this->registrant->registerExtensionMethods();
        $response->deleteCookie('foo');
        $this->assertSame('foo=; Max-Age=0; HttpOnly', $response->getHeaders()->getFirst('Set-Cookie'));
    }

    /**
     * @dataProvider provideExtensionMethods
     * @param string $methodName The name of the extension method
     */
    public function testRegisterRegistersExtensionMethods(string $methodName): void
    {
        $response = $this->createMock(IResponse::class);
        $this->assertNull(ExtensionMethodRegistry::getExtensionMethod($response, $methodName));
        // The previous call would've memoized a null closure, so reset it
        ExtensionMethodRegistry::reset();
        $this->registrant->registerExtensionMethods();
        $this->assertInstanceOf(Closure::class, ExtensionMethodRegistry::getExtensionMethod($response, $methodName));
    }
}
