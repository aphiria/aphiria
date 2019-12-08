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

use InvalidArgumentException;
use Throwable;

/**
 * Defines the exception that's thrown when validation fails
 */
final class ValidationException extends InvalidArgumentException
{
    /** @var ValidationContext The context that the error occurred in */
    private ValidationContext $validationContext;

    /**
     * @inheritdoc
     * @param ValidationContext $validationContext The context that the error occurred in
     */
    public function __construct(ValidationContext $validationContext, $message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->validationContext = $validationContext;
    }

    /**
     * Gets the context that the error occurred in
     *
     * @return ValidationContext The context that the error occurred in
     */
    public function getValidationContext(): ValidationContext
    {
        return $this->validationContext;
    }
}
