<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Configuration\Api\Bootstrappers;

use Aphiria\Api\Controllers\ControllerParameterResolver;
use Aphiria\Api\Controllers\IRouteActionInvoker;
use Aphiria\Api\Controllers\RouteActionInvoker;
use Aphiria\Api\Validation\RequestBodyValidator;
use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Net\Http\ContentNegotiation\IContentNegotiator;
use Aphiria\Net\Http\ContentNegotiation\INegotiatedResponseFactory;
use Aphiria\Validation\ErrorMessages\IErrorMessageInterpolator;
use Aphiria\Validation\IValidator;

/**
 * Defines the bootstrapper for controllers
 */
final class ControllerBootstrapper extends Bootstrapper
{
    /**
     * @inheritdoc
     */
    public function registerBindings(IContainer $container): void
    {
        $requestBodyValidator = new RequestBodyValidator(
            $container->resolve(IValidator::class),
            $container->resolve(IErrorMessageInterpolator::class)
        );
        $controllerParameterResolver = new ControllerParameterResolver($container->resolve(IContentNegotiator::class));
        $routeActionInvoker = new RouteActionInvoker(
            $container->resolve(IContentNegotiator::class),
            $requestBodyValidator,
            $container->resolve(INegotiatedResponseFactory::class),
            $controllerParameterResolver
        );
        $container->bindInstance(IRouteActionInvoker::class, $routeActionInvoker);
    }
}
