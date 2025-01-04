<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Controllers;

/**
 * Defines the interface for route action parameter deserializers to implement
 */
interface IRequestParameterDeserializer
{
    /**
     * Deserializes a route action parameter
     *
     * @param string $type The type to deserialize to
     * @param mixed $value The serialized value to deserialize
     * @return mixed The deserialized route action parameter
     * @throws FailedRequestParameterConversionException Thrown if the value could not be deserialized
     */
    public function deserializeRouteActionParameter(string $type, mixed $value): mixed;
}
