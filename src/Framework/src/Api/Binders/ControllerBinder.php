<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Api\Binders;

use Aphiria\Api\Controllers\ControllerParameterResolver;
use Aphiria\Api\Controllers\IRouteActionInvoker;
use Aphiria\Api\Controllers\RouteActionInvoker;
use Aphiria\Api\Validation\RequestBodyValidator;
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
        $requestBodyValidator = new RequestBodyValidator(
            $container->resolve(IValidator::class),
            $container->resolve(IErrorMessageInterpolator::class)
        );
        $controllerParameterResolver = new ControllerParameterResolver($container->resolve(IContentNegotiator::class));
        $routeActionInvoker = new RouteActionInvoker(
            $container->resolve(IContentNegotiator::class),
            $requestBodyValidator,
            $container->resolve(IResponseFactory::class),
            $controllerParameterResolver
        );
        $container->bindInstance(IRouteActionInvoker::class, $routeActionInvoker);
    }
}
