<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Validation;

use Aphiria\Net\Http\IHttpRequestMessage;

/**
 * Defines the interface for request body validators to implement
 */
interface IRequestBodyValidator
{
    /**
     * Validates the negotiated request body
     *
     * @param IHttpRequestMessage $request The request whose body we're validating
     * @param mixed $body The deserialized body to validate
     * @throws InvalidRequestBodyException Thrown if the body is not valid
     */
    public function validate(IHttpRequestMessage $request, $body): void;
}
