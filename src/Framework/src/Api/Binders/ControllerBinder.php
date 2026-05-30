<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2026 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Api\Binders;

use Aphiria\Api\Controllers\ControllerParameterResolver;
use Aphiria\Api\Controllers\FailedRequestParameterConversionException;
use Aphiria\Api\Controllers\IRequestParameterDeserializer;
use Aphiria\Api\Controllers\IRouteActionInvoker;
use Aphiria\Api\Controllers\RequestParameterDeserializer;
use Aphiria\Api\Controllers\RouteActionInvoker;
use Aphiria\Api\Validation\RequestBodyValidator;
use Aphiria\Application\Configuration\GlobalConfiguration;
use Aphiria\Application\Configuration\MissingConfigurationValueException;
use Aphiria\ContentNegotiation\IBodyDeserializer;
use Aphiria\ContentNegotiation\IContentNegotiator;
use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Net\Http\IResponseFactory;
use Aphiria\Validation\ErrorMessages\IErrorMessageInterpolator;
use Aphiria\Validation\IValidator;
use DateTime;
use DateTimeImmutable;

/**
 * Defines the binder for controllers
 */
final class ControllerBinder extends Binder
{
    /**
     * @inheritdoc
     * @throws MissingConfigurationValueException Thrown if the date format config value does not exist
     */
    public function bind(IContainer $container): void
    {
        $requestBodyValidator = new RequestBodyValidator(
            $container->resolve(IValidator::class),
            $container->resolve(IErrorMessageInterpolator::class),
        );
        $controllerParameterResolver = new ControllerParameterResolver($container->resolve(IBodyDeserializer::class));
        $routeActionInvoker = new RouteActionInvoker(
            $container->resolve(IContentNegotiator::class),
            $requestBodyValidator,
            $container->resolve(IResponseFactory::class),
            $controllerParameterResolver,
        );
        $container->bindInstance(IRouteActionInvoker::class, $routeActionInvoker);
        $container->bindInstance(IRequestParameterDeserializer::class, $this->getRequestParameterDeserializer($container));
    }

    /**
     * Gets the request parameter deserializer
     *
     * @param IContainer $container The DI container
     * @return IRequestParameterDeserializer The request parameter deserializer
     * @throws MissingConfigurationValueException Thrown if the date format config value does not exist
     */
    protected function getRequestParameterDeserializer(IContainer $container): IRequestParameterDeserializer
    {
        $deserializer = new RequestParameterDeserializer();
        $dateFormat = GlobalConfiguration::getString('aphiria.serialization.dateFormat');
        $dateTimeFormat = GlobalConfiguration::getString('aphiria.serialization.dateTimeFormat');
        $deserializer->registerDeserializer(
            DateTime::class,
            function (mixed $value) use ($dateTimeFormat, $dateFormat): DateTime {
                if (($dateTime = DateTime::createFromFormat($dateTimeFormat, $value)) instanceof DateTime) {
                    return $dateTime;
                }

                if (($date = DateTime::createFromFormat($dateFormat, $value)) instanceof DateTime) {
                    return $date;
                }

                throw new FailedRequestParameterConversionException("Could not convert \"$value\" to " . DateTime::class);
            },
        );
        $deserializer->registerDeserializer(
            DateTimeImmutable::class,
            function (mixed $value) use ($dateTimeFormat, $dateFormat): DateTimeImmutable {
                if (($dateTime = DateTimeImmutable::createFromFormat($dateTimeFormat, $value)) instanceof DateTimeImmutable) {
                    return $dateTime;
                }

                if (($date = DateTimeImmutable::createFromFormat($dateFormat, $value)) instanceof DateTimeImmutable) {
                    return $date;
                }

                throw new FailedRequestParameterConversionException("Could not convert \"$value\" to " . DateTimeImmutable::class);
            },
        );

        return $deserializer;
    }
}
