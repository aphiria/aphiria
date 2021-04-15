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
use Aphiria\Framework\Net\ExtensionMethods\BodyExtensionMethodRegistrant;
use Aphiria\Net\Http\IBody;
use Closure;
use PHPUnit\Framework\TestCase;

class BodyExtensionMethodRegistrantTest extends TestCase
{
    private BodyExtensionMethodRegistrant $registrant;

    protected function setUp(): void
    {
        ExtensionMethodRegistry::reset();
        $this->registrant = new BodyExtensionMethodRegistrant();
    }

    public function provideExtensionMethods(): array
    {
        return [
            ['getMimeType'],
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
        $body = $this->createMock(IBody::class);
        $this->assertNull(ExtensionMethodRegistry::getExtensionMethod($body, $methodName));
        // The previous call would've memoized a null closure, so reset it
        ExtensionMethodRegistry::reset();
        $this->registrant->registerExtensionMethods();
        $this->assertInstanceOf(Closure::class, ExtensionMethodRegistry::getExtensionMethod($body, $methodName));
    }
}
