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
    /** @var string[] The list of object hash IDs we'll use to detect circular dependencies */
    private array $objectHashIds = [];

    /**
     * @param mixed $value The value being validated
     * @param ErrorCollection|null $errors The errors that have occurred during validation
     * @throws CircularDependencyException Thrown if a circular dependency was detected
     */
    public function __construct($value, ErrorCollection $errors = null)
    {
        $this->value = $value;
        $this->errors = $errors ?? new ErrorCollection();

        // TODO: I think I need a concept of a parent context in the case of recursive validation.  If that's the case, I'll need to change the circular dependency logic to invoke a method recursively up the parent context chain.

        if (\is_object($this->value)) {
            $objectHashId = \spl_object_hash($this->value);

            if (isset($this->objectHashIds[$objectHashId])) {
                throw new CircularDependencyException('Circular dependency on ' . \get_class($value) . ' detected');
            }

            $this->objectHashIds[\spl_object_hash($this->value)] = true;
        }
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
