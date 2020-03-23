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
use Aphiria\Framework\Exceptions\Http\HttpExceptionRenderer;
use Aphiria\Net\Http\IResponseFactory;
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
        /** @var HttpExceptionRenderer|null $httpExceptionRenderer */
        $httpExceptionRenderer = null;

        if ($container->tryResolve(HttpExceptionRenderer::class, $httpExceptionRenderer)) {
            /** @var IHttpRequestMessage|null $request */
            $request = null;

            if ($container->tryResolve(IHttpRequestMessage::class, $request)) {
                $httpExceptionRenderer->setRequest($request);
            }

            /** @var IResponseFactory|null */
            $responseFactory = null;

            if ($container->tryResolve(IResponseFactory::class, $responseFactory)) {
                $httpExceptionRenderer->setResponseFactory($responseFactory);
            }
        }
    }
}
