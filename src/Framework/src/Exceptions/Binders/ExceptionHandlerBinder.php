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
use Aphiria\Framework\Api\Exceptions\IApiExceptionRenderer;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponseFactory;

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
        /** @var IApiExceptionRenderer|null $apiExceptionRenderer */
        $apiExceptionRenderer = null;

        if ($container->tryResolve(IApiExceptionRenderer::class, $apiExceptionRenderer)) {
            /** @var IRequest|null $request */
            $request = null;

            if ($container->tryResolve(IRequest::class, $request)) {
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
