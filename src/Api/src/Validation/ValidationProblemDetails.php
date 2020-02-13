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

use Aphiria\Api\Errors\ProblemDetails;
use Aphiria\Net\Http\HttpStatusCodes;

/**
 * Defines the problem details for validation errors
 */
final class ValidationProblemDetails extends ProblemDetails
{
    /** @var string[] The list of error messages that describe why the body is invalid */
    public array $errors;

    /**
     * @inheritdoc
     * @param string[] $errors The list of errors that describe what was invalid
     */
    public function __construct(
        array $errors,
        string $type = 'https://tools.ietf.org/html/rfc7231#section-6.5.1',
        string $title = 'One or more validation errors occurred',
        string $detail = null,
        int $status = HttpStatusCodes::HTTP_BAD_REQUEST,
        string $instance = null
    ) {
        parent::__construct($type, $title, $detail, $status, $instance);

        $this->errors = $errors;
    }
}
