<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Net\Binders;

use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\Request;
use Aphiria\Net\Http\RequestFactory;
use Aphiria\Net\Uri;

/**
 * Defines the request binder
 */
class RequestBinder extends Binder
{
    /** @var bool Whether or not the app is running in the console */
    protected bool $isRunningInConsole {
        get => \PHP_SAPI === 'cli' || \PHP_SAPI === 'phpdbg';
    }
    /** @var IRequest The current request */
    protected IRequest $request {
        get {
            // The $_SERVER superglobal will not have enough info to construct a request when running from the console
            if ($this->isRunningInConsole) {
                return new Request('GET', new Uri('http://localhost'));
            }

            /** @var array<string, mixed> $_SERVER */
            return (new RequestFactory())->createRequestFromSuperglobals($_SERVER);
        }
    }
    /** @var IRequest|null The overriding request if one is set (useful for integration tests) */
    private static ?IRequest $overridingRequest = null;

    /**
     * Sets the request to use (useful for integration tests)
     *
     * @param IRequest $overridingRequest The request to use
     */
    public static function setOverridingRequest(IRequest $overridingRequest): void
    {
        self::$overridingRequest = $overridingRequest;
    }

    /**
     * @inheritdoc
     */
    public function bind(IContainer $container): void
    {
        // Integration tests might have overridden the request to use
        // We use a factory so that a new instance is passed in whenever needed (allows support for handling concurrent requests)
        $container->bindFactory(IRequest::class, fn (): IRequest => self::$overridingRequest ?? $this->request);
    }
}
