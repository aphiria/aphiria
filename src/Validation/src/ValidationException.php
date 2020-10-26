<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
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
    /** @var ConstraintViolation[] The violations that occurred */
    private array $violations;

    /**
     * @inheritdoc
     * @param ConstraintViolation[] $violations The violations that occurred
     */
    public function __construct(array $violations, string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->violations = $violations;
    }

    /**
     * Gets the violations that occurred
     *
     * @return ConstraintViolation[] The list of violations
     */
    public function getViolations(): array
    {
        return $this->violations;
    }
}
