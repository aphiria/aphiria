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
use Aphiria\Net\Http\IHttpRequestMessage;
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
        $container->bindInstance(IHttpRequestMessage::class, $this->getRequest());
    }

    /**
     * Gets the current request
     *
     * @return IHttpRequestMessage The request
     */
    protected function getRequest(): IHttpRequestMessage
    {
        // The $_SERVER superglobal will not have enough info to construct a request when running from the console
        if (\PHP_SAPI === 'cli' || \PHP_SAPI === 'phpdbg') {
            return new Request('GET', new Uri('http://localhost'));
        }

        return (new RequestFactory)->createRequestFromSuperglobals($_SERVER);
    }
}
