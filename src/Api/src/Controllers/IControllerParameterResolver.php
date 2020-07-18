<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Controllers;

use Aphiria\Net\Http\IRequest;
use ReflectionParameter;

/**
 * Defines the interface for controller parameter resolvers to implement
 */
interface IControllerParameterResolver
{
    /**
     * Resolved a controller parameter
     *
     * @param ReflectionParameter $reflectionParameter The reflected parameter
     * @param IRequest $request The current request
     * @param array $routeVariables The list of route variables
     * @return mixed The resolved parameter value
     * @throws FailedRequestContentNegotiationException Thrown if the request content negotiation failed
     * @throws MissingControllerParameterValueException Thrown if there was no valid value for the parameter
     * @throws RequestBodyDeserializationException Thrown if the request body could not be deserialized
     * @throws FailedScalarParameterConversionException Thrown if a scalar parameter could not be converted
     */
    public function resolveParameter(
        ReflectionParameter $reflectionParameter,
        IRequest $request,
        array $routeVariables
    );
}
