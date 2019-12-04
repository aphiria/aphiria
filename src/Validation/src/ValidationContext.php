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

/**
 * Defines the context that validation occurs in
 */
final class ValidationContext
{
    /** @var mixed The value being validated */
    private $value;
    /** @var ErrorCollection The errors that have occurred during validation */
    private ErrorCollection $errors;

    /**
     * @param mixed $value The value being validated
     * @param ErrorCollection|null $errors The errors that have occurred during validation
     */
    public function __construct($value, ErrorCollection $errors = null)
    {
        $this->value = $value;
        $this->errors = $errors ?? new ErrorCollection();
    }

    /**
     * Gets the errors that have occurred during validation
     *
     * @return ErrorCollection The errors that have occurred
     */
    public function getErrors(): ErrorCollection
    {
        return $this->errors;
    }

    /**
     * Gets the value being validated
     *
     * @return mixed The value being validated
     */
    public function getValue()
    {
        return $this->value;
    }
}
