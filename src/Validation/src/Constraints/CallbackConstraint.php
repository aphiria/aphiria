<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints;

/**
 * Defines the callback constraint
 */
class CallbackConstraint extends Constraint
{
    /** @var string The default error message ID */
    private const DEFAULT_ERROR_MESSAGE_ID = 'Field must pass callback';
    /** @var callable(mixed): bool The callback to run */
    private $callback;

    /**
     * @inheritdoc
     * @param callable(mixed): bool $callback The callback to execute
     */
    public function __construct(callable $callback, string $errorMessageId = self::DEFAULT_ERROR_MESSAGE_ID)
    {
        parent::__construct($errorMessageId);

        $this->callback = $callback;
    }

    /**
     * @inheritdoc
     */
    public function passes($value): bool
    {
        return ($this->callback)($value);
    }
}
