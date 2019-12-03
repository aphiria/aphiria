<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation;

use Aphiria\Validation\Rules\Errors\ErrorCollection;
use InvalidArgumentException;
use Throwable;

/**
 * Defines the exception that's thrown when validation fails
 */
final class ValidationException extends InvalidArgumentException
{
    /** @var ErrorCollection The error collection from the exception */
    private ErrorCollection $errors;

    /**
     * @inheritdoc
     * @param ErrorCollection $errors The errors from validation
     */
    public function __construct(ErrorCollection $errors, $message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->errors = $errors;
    }

    /**
     * Gets the errors from validation
     *
     * @return ErrorCollection The errors from validation
     */
    public function getErrors(): ErrorCollection
    {
        return $this->errors;
    }
}
