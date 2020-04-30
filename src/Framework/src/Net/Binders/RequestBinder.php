<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
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
    /**
     * @inheritdoc
     */
    public function bind(IContainer $container): void
    {
        // Integration tests might set the request outside of a binder, so don't reset it if it is already bound
        if (!$container->hasBinding(IRequest::class)) {
            $container->bindInstance(IRequest::class, $this->getRequest());
        }
    }

    /**
     * Gets the current request
     *
     * @return IRequest The request
     */
    protected function getRequest(): IRequest
    {
        // The $_SERVER superglobal will not have enough info to construct a request when running from the console
        if ($this->isRunningInConsole()) {
            return new Request('GET', new Uri('http://localhost'));
        }

        return (new RequestFactory())->createRequestFromSuperglobals($_SERVER);
    }

    /**
     * Gets whether or not we're running in the console
     *
     * @return bool True if running in console, otherwise false
     */
    protected function isRunningInConsole(): bool
    {
        return \PHP_SAPI === 'cli' || \PHP_SAPI === 'phpdbg';
    }
}
