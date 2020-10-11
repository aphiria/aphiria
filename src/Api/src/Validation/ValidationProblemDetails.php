<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Validation;

use Aphiria\Api\Errors\ProblemDetails;
use Aphiria\Net\Http\HttpStatusCodes;

/**
 * Defines the problem details for validation errors
 */
final class ValidationProblemDetails extends ProblemDetails
{
    /**
     * @inheritdoc
     * @param string[] $errors The list of errors that describe what was invalid
     */
    public function __construct(
        public array $errors,
        string $type = 'https://tools.ietf.org/html/rfc7231#section-6.5.1',
        string $title = 'One or more validation errors occurred',
        string $detail = null,
        int $status = HttpStatusCodes::BAD_REQUEST,
        string $instance = null
    ) {
        parent::__construct($type, $title, $detail, $status, $instance);
    }
}
