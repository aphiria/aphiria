<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Net\Binders;

use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Net\Binders\ResponseWriterBinder;
use Aphiria\Net\Http\IResponseWriter;
use Aphiria\Net\Http\StreamResponseWriter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ResponseWriterBinderTest extends TestCase
{
    private IContainer&MockObject $container;

    protected function setUp(): void
    {
        $this->container = $this->createMock(IContainer::class);
    }

    public function testBindBindsInstanceOfStreamResponseWriter(): void
    {
        $this->container->method('bindInstance')
            ->with(IResponseWriter::class, $this->callback(function (IResponseWriter $responseWriter): bool {
                return $responseWriter instanceof StreamResponseWriter;
            }));
        new ResponseWriterBinder()->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }
}
