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

use Aphiria\Validation\Rules\RulesFactory;

/**
 * Defines the validator factory
 */
final class ValidatorFactory implements IValidatorFactory
{
    /** @var RulesFactory The rules factory */
    protected RulesFactory $rulesFactory;

    /**
     * @param RulesFactory|null $rulesFactory The rules factory
     */
    public function __construct(RulesFactory $rulesFactory = null)
    {
        $this->rulesFactory = $rulesFactory ?? new RulesFactory();
    }

    /**
     * @inheritdoc
     */
    public function createValidator(): IValidator
    {
        return new Validator($this->rulesFactory);
    }
}
