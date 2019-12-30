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

use Aphiria\Api\Errors\ProblemDetails;

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
        string $type = null,
        string $title = null,
        string $detail = null,
        int $status = null,
        string $instance = null
    ) {
        parent::__construct($type, $title, $detail, $status, $instance);

        $this->errors = $errors;
    }
}
