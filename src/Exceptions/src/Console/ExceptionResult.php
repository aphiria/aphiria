<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Exceptions\Console;

use InvalidArgumentException;

/**
 * Defines the result of an exception in a console application
 */
class ExceptionResult
{
    /** @var int The resulting status code */
    private int $statusCode;
    /** @var string[] The list of messages to write */
    private array $messages;

    /**
     * @param int $statusCode The resulting status code
     * @param string|string[] $messages The message or list of messages to write
     */
    public function __construct(int $statusCode, $messages)
    {
        if ($statusCode < 0) {
            $this->statusCode = 0;
        } elseif ($statusCode >= 255) {
            $this->statusCode = 254;
        } else {
            $this->statusCode = $statusCode;
        }

        if (\is_string($messages)) {
            $this->messages = [$messages];
        } elseif (\is_array($messages)) {
            $this->messages = $messages;
        } else {
            throw new InvalidArgumentException('Messages must be a string or array of strings');
        }
    }

    /**
     * Gets the list of messages to write
     *
     * @return string[] The list of messages
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Gets the status code
     *
     * @return int The resulting status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
