<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Validation;

/**
 * Defines the interface for request body validators to implement
 */
interface IRequestBodyValidator
{
    /**
     * Validates the negotiated request body
     *
     * @param object $body The deserialized body to validate
     * @throws InvalidRequestBodyException Thrown if the body is not valid
     */
    public function validate(object $body): void;
}
