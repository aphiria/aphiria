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

    /**
     * @inheritdoc
     * @param non-empty-string $regex The regular expression to run
     * @throws InvalidArgumentException Thrown if the regex is empty
     */
    public function __construct(private readonly string $regex, string $errorMessageId = self::DEFAULT_ERROR_MESSAGE_ID)
    {
        parent::__construct($errorMessageId);

        if (empty($this->regex)) {
            throw new InvalidArgumentException('Regex cannot be empty');
        }
    }

    /**
     * @inheritdoc
     */
    public function passes(mixed $value): bool
    {
        return \preg_match($this->regex, (string)$value) === 1;
    }
}
