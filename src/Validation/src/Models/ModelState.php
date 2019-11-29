<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Models;

use Aphiria\Validation\IValidatorFactory;
use Aphiria\Validation\IValidator;
use Aphiria\Validation\Rules\Errors\ErrorCollection;
use Aphiria\Validation\ValidatorFactory;

/**
 * Defines a model state
 */
abstract class ModelState
{
    /** @var IValidatorFactory The validator factory */
    protected IValidatorFactory $validatorFactory;
    /** @var bool Whether or not the model state is valid */
    protected bool $isValid = false;
    /** @var ErrorCollection The list of errors */
    protected ErrorCollection $errors;

    /**
     * @param object $model The model being validated
     * @param IValidatorFactory|null $validatorFactory The validator factory
     */
    public function __construct(object $model, IValidatorFactory $validatorFactory = null)
    {
        $this->validatorFactory = $validatorFactory ?? new ValidatorFactory();
        $validator = $this->validatorFactory->createValidator();
        $this->registerFields($validator);
        $this->isValid = $validator->isValid($this->getModelProperties($model));
        $this->errors = $validator->getErrors();
    }

    /**
     * Gets the errors, if there are any
     *
     * @return ErrorCollection The errors
     */
    public function getErrors(): ErrorCollection
    {
        return $this->errors;
    }

    /**
     * Gets whether or not the model is valid
     *
     * @return bool True if the model is valid, otherwise false
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * Gets the mapping of model property names => model property values
     *
     * @param object $model The model being validated
     * @return array The mapping of property names => property values
     */
    abstract protected function getModelProperties($model): array;

    /**
     * Registers rules for fields in the model
     *
     * @param IValidator $validator The validator to register with
     */
    abstract protected function registerFields(IValidator $validator): void;
}
