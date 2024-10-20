<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Validation;

use Aphiria\Api\Errors\ProblemDetails;
use Aphiria\Net\Http\HttpStatusCode;

/**
 * Defines the problem details for validation errors
 */
final class ValidationProblemDetails extends ProblemDetails
{
    /**
     * @inheritdoc
     * @param list<string> $errors The list of errors that describe what was invalid
     */
    public function __construct(
        public array $errors,
        string $type = 'https://tools.ietf.org/html/rfc7231#section-6.5.1',
        string $title = 'One or more validation errors occurred',
        ?string $detail = null,
        HttpStatusCode|int $status = HttpStatusCode::BadRequest,
        ?string $instance = null
    ) {
        parent::__construct($type, $title, $detail, $status, $instance);
    }
}
