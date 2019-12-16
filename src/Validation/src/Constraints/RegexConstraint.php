<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints;

use Aphiria\Validation\ValidationContext;

/**
 * Defines a regular expression constraint
 */
class RegexConstraint extends ValidationConstraint
{
    /** @var string The regular expression to run */
    private string $regex;

    /**
     * @inheritdoc
     * @param string $regex The regular expression to run
     */
    public function __construct(string $regex, string $errorMessageId)
    {
        parent::__construct($errorMessageId);

        $this->regex = $regex;
    }

    /**
     * @inheritdoc
     */
    public function passes($value, ValidationContext $validationContext): bool
    {
        return preg_match($this->regex, $value) === 1;
    }
}
