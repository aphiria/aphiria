<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints;

/**
 * Defines a regular expression constraint
 */
class RegexConstraint extends Constraint
{
    /** @var string The default error message ID */
    private const DEFAULT_ERROR_MESSAGE_ID = 'Field is invalid';
    /** @var string The regular expression to run */
    private string $regex;

    /**
     * @inheritdoc
     * @param string $regex The regular expression to run
     */
    public function __construct(string $regex, string $errorMessageId = self::DEFAULT_ERROR_MESSAGE_ID)
    {
        parent::__construct($errorMessageId);

        $this->regex = $regex;
    }

    /**
     * @inheritdoc
     */
    public function passes($value): bool
    {
        return preg_match($this->regex, $value) === 1;
    }
}
