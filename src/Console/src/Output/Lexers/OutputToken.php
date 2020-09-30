<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Lexers;

/**
 * Defines a output token
 */
final class OutputToken
{
    /** @var string The token type */
    public string $type;
    /** @var mixed The value of the token */
    public $value;
    /** @var int The position of the token in the original text */
    public int $position;

    /**
     * @param string $type The token type
     * @param mixed $value The value of the token
     * @param int $position The position of the token in the original text
     */
    public function __construct(string $type, mixed $value, int $position)
    {
        $this->type = $type;
        $this->value = $value;
        $this->position = $position;
    }
}
