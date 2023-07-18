<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints;

use InvalidArgumentException;

/**
 * Defines a regular expression constraint
 */
class RegexConstraint extends Constraint
{
    /** @var string The default error message ID */
    private const DEFAULT_ERROR_MESSAGE_ID = 'Field is invalid';
    /** @var non-empty-string The regular expression to run */
    private readonly string $regex;

    /**
     * @inheritdoc
     * @param string $regex The regular expression to run
     * @throws InvalidArgumentException Thrown if the regex is empty
     */
    public function __construct(string $regex, string $errorMessageId = self::DEFAULT_ERROR_MESSAGE_ID)
    {
        parent::__construct($errorMessageId);

        if (empty($regex)) {
            throw new InvalidArgumentException('Regex cannot be empty');
        }

        $this->regex = $regex;
    }

    /**
     * @inheritdoc
     */
    public function passes(mixed $value): bool
    {
        return \preg_match($this->regex, (string)$value) === 1;
    }
}
