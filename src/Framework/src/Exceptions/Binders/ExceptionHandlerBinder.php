<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Exceptions\Binders;

use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Exceptions\Http\HttpExceptionHandler;
use Aphiria\Net\Http\ContentNegotiation\INegotiatedResponseFactory;
use Aphiria\Net\Http\IHttpRequestMessage;

/**
 * Defines the exception handler binder
 */
class ExceptionHandlerBinder extends Binder
{
    /**
     * @inheritdoc
     */
    public function bind(IContainer $container): void
    {
        /** @var HttpExceptionHandler|null $httpExceptionHandler */
        $httpExceptionHandler = null;

        if ($container->tryResolve(HttpExceptionHandler::class, $httpExceptionHandler)) {
            /** @var IHttpRequestMessage|null $request */
            $request = null;

            if ($container->tryResolve(IHttpRequestMessage::class, $request)) {
                $httpExceptionHandler->setRequest($request);
            }

            /** @var INegotiatedResponseFactory|null */
            $negotiatedResponseFactory = null;

            if ($container->tryResolve(INegotiatedResponseFactory::class, $negotiatedResponseFactory)) {
                $httpExceptionHandler->setNegotiatedResponseFactory($negotiatedResponseFactory);
            }
        }
    }
}
