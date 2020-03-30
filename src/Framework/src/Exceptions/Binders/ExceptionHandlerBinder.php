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
use Aphiria\Framework\Api\Exceptions\ApiExceptionRenderer;
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
        /** @var ApiExceptionRenderer|null $apiExceptionRenderer */
        $apiExceptionRenderer = null;

        if ($container->tryResolve(ApiExceptionRenderer::class, $apiExceptionRenderer)) {
            /** @var IHttpRequestMessage|null $request */
            $request = null;

            if ($container->tryResolve(IHttpRequestMessage::class, $request)) {
                $apiExceptionRenderer->setRequest($request);
            }

            /** @var IResponseFactory|null */
            $responseFactory = null;

            if ($container->tryResolve(IResponseFactory::class, $responseFactory)) {
                $apiExceptionRenderer->setResponseFactory($responseFactory);
            }
        }
    }
}
