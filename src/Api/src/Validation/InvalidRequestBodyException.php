<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
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
    /** @var list<string> The list of error messages that describe why the body is invalid */
    public readonly array $errors;

    /**
     * @inheritdoc
     * @param list<string> $errors The list of error messages that describe why the body is invalid
     */
    public function __construct(array $errors, string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->errors = $errors;
    }
}
