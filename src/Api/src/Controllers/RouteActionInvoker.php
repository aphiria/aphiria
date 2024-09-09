<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Controllers;

use Aphiria\Api\Validation\IRequestBodyValidator;
use Aphiria\ContentNegotiation\ContentNegotiator;
use Aphiria\ContentNegotiation\IContentNegotiator;
use Aphiria\ContentNegotiation\NegotiatedBodyDeserializer;
use Aphiria\ContentNegotiation\NegotiatedResponseFactory;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpStatusCode;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\IResponseFactory;
use Aphiria\Net\Http\Response;
use Closure;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;

/**
 * Defines the route action invoker
 */
class RouteActionInvoker implements IRouteActionInvoker
{
    /** @const The name of the property to store the parsed body in */
    private const string PARSED_BODY_PROPERTY_NAME = '__APHIRIA_PARSED_BODY';
    /** @var IControllerParameterResolver The controller parameter resolver to use */
    private readonly IControllerParameterResolver $controllerParameterResolver;
    /** @var IResponseFactory The response factory */
    private readonly IResponseFactory $responseFactory;

    /**
     * @param IContentNegotiator $contentNegotiator The content negotiator
     * @param IRequestBodyValidator|null $requestBodyValidator The validator for request bodies, or null if we aren't validating them
     * @param IResponseFactory|null $responseFactory The response factory
     * @param IControllerParameterResolver|null $controllerParameterResolver The controller parameter resolver to use
     */
    public function __construct(
        IContentNegotiator $contentNegotiator = new ContentNegotiator(),
        private readonly ?IRequestBodyValidator $requestBodyValidator = null,
        ?IResponseFactory $responseFactory = null,
        ?IControllerParameterResolver $controllerParameterResolver = null
    ) {
        $this->responseFactory = $responseFactory ?? new NegotiatedResponseFactory($contentNegotiator);
        $this->controllerParameterResolver = $controllerParameterResolver
            ?? new ControllerParameterResolver(new NegotiatedBodyDeserializer($contentNegotiator));
    }

    /**
     * @inheritdoc
     */
    public function invokeRouteAction(
        Closure $routeActionDelegate,
        IRequest $request,
        array $routeVariables
    ): IResponse {
        try {
            $reflectionFunction = $this->reflectRouteActionDelegate($routeActionDelegate);
        } catch (ReflectionException $ex) {
            throw new HttpException(
                HttpStatusCode::InternalServerError,
                'Failed to reflect controller',
                0,
                $ex
            );
        }

        $resolvedParameters = [];

        try {
            foreach ($reflectionFunction->getParameters() as $reflectionParameter) {
                /** @psalm-suppress MixedAssignment The resolved parameter could legitimately be mixed */
                $resolvedParameter = $this->controllerParameterResolver->resolveParameter(
                    $reflectionParameter,
                    $request,
                    $routeVariables
                );

                $this->requestBodyValidator?->validate($request, $resolvedParameter);

                /** @psalm-suppress MixedAssignment The resolved parameter could legitimately be mixed */
                $resolvedParameters[] = $resolvedParameter;

                if (\is_object($resolvedParameter)) {
                    $request->properties->add(self::PARSED_BODY_PROPERTY_NAME, $resolvedParameter);
                }
            }
        } catch (MissingControllerParameterValueException | FailedScalarParameterConversionException $ex) {
            throw new HttpException(
                HttpStatusCode::BadRequest,
                'Failed to invoke controller',
                0,
                $ex
            );
        } catch (FailedRequestContentNegotiationException $ex) {
            throw new HttpException(
                HttpStatusCode::UnsupportedMediaType,
                'Failed to invoke controller',
                0,
                $ex
            );
        } catch (RequestBodyDeserializationException $ex) {
            throw new HttpException(
                HttpStatusCode::UnprocessableEntity,
                'Failed to invoke controller',
                0,
                $ex
            );
        }

        /** @var array|float|int|object|string|null $actionResult */
        $actionResult = $routeActionDelegate(...$resolvedParameters);

        if ($actionResult instanceof IResponse) {
            return $actionResult;
        }

        // Handle void return types
        if ($actionResult === null) {
            return new Response(HttpStatusCode::NoContent);
        }

        // Attempt to create an OK response from the return value
        return $this->responseFactory->createResponse(
            $request,
            HttpStatusCode::Ok,
            null,
            $actionResult
        );
    }

    /**
     * Reflects a route action delegate
     *
     * @param Closure $routeActionDelegate The route action delegate to reflect
     * @return ReflectionFunctionAbstract The reflected method/function
     * @throws ReflectionException Thrown if there was an error reflecting the delegate
     * @note This is split out primarily for testability
     * @internal
     */
    protected function reflectRouteActionDelegate(Closure $routeActionDelegate): ReflectionFunctionAbstract
    {
        return new ReflectionFunction($routeActionDelegate);
    }
}
