<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Api\Binders;

use Aphiria\Api\Controllers\NegotiatedContentControllerParameterResolver;
use Aphiria\Api\Controllers\IRouteActionInvoker;
use Aphiria\Api\Controllers\NegotiatedContentRouteActionInvoker;
use Aphiria\Api\Validation\InterpolatedErrorMessageRequestBodyValidator;
use Aphiria\ContentNegotiation\IContentNegotiator;
use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Net\Http\IResponseFactory;
use Aphiria\Validation\ErrorMessages\IErrorMessageInterpolator;
use Aphiria\Validation\IValidator;

/**
 * Defines the binder for controllers
 */
final class ControllerBinder extends Binder
{
    /**
     * @inheritdoc
     */
    public function bind(IContainer $container): void
    {
        $requestBodyValidator = new InterpolatedErrorMessageRequestBodyValidator(
            $container->resolve(IValidator::class),
            $container->resolve(IErrorMessageInterpolator::class)
        );
        $controllerParameterResolver = new NegotiatedContentControllerParameterResolver($container->resolve(IContentNegotiator::class));
        $routeActionInvoker = new NegotiatedContentRouteActionInvoker(
            $container->resolve(IContentNegotiator::class),
            $requestBodyValidator,
            $container->resolve(IResponseFactory::class),
            $controllerParameterResolver
        );
        $container->bindInstance(IRouteActionInvoker::class, $routeActionInvoker);
    }
}
