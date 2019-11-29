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

/**
 * Defines the interface for validator factories to implement
 */
interface IValidatorFactory
{
    /**
     * Creates a new validator
     *
     * @return IValidator The validator
     */
    public function createValidator(): IValidator;
}
