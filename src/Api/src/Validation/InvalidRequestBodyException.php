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

use InvalidArgumentException;
use Throwable;

/**
 * Defines the exception that's thrown when the request body is invalid
 */
final class InvalidRequestBodyException extends InvalidArgumentException
{
    /** @var string[] The list of error messages that describe why the body is invalid */
    private array $errors;

    /**
     * @inheritdoc
     * @param string[] $errors The list of error messages that describe why the body is invalid
     */
    public function __construct(array $errors, string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->errors = $errors;
    }

    /**
     * Gets the list of errors that describe why the body is invalid
     *
     * @return string[] The list of errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
