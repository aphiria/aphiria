<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation;

use InvalidArgumentException;
use Throwable;

/**
 * Defines the exception that's thrown when validation fails
 */
final class ValidationException extends InvalidArgumentException
{
    /**
     * @inheritdoc
     * @param list<ConstraintViolation> $violations The violations that occurred
     */
    public function __construct(public readonly array $violations, string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
