<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests;

use Aphiria\Api\App;
use Aphiria\Api\Tests\Mocks\AttributeMiddleware;
use Aphiria\DependencyInjection\IDependencyResolver;
use Aphiria\Middleware\IMiddleware;
use Aphiria\Middleware\MiddlewareCollection;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the app
 */
class AppTest extends TestCase
{
    private App $app;
    /** @var IDependencyResolver|MockObject */
    private IDependencyResolver $dependencyResolver;
    /** @var IRequestHandler|MockObject */
    private IRequestHandler $kernel;

    protected function setUp(): void
    {
        $this->dependencyResolver = $this->createMock(IDependencyResolver::class);
        $this->kernel = $this->createMock(IRequestHandler::class);
        $this->app = new App($this->dependencyResolver, $this->kernel, new MiddlewareCollection());
    }
}
