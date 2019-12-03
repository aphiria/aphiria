<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Rules;

use Aphiria\Validation\ValidationContext;
use InvalidArgumentException;
use LogicException;

/**
 * Defines a regular expression rule
 */
class RegexRule implements IRuleWithArgs
{
    /** @var string|null The regular expression to run */
    protected ?string $regex = null;

    /**
     * @inheritdoc
     */
    public function getSlug(): string
    {
        return 'regex';
    }

    /**
     * @inheritdoc
     */
    public function passes($value, ValidationContext $validationContext): bool
    {
        if ($this->regex === null) {
            throw new LogicException('Regex not set');
        }

        return preg_match($this->regex, $value) === 1;
    }

    /**
     * @inheritdoc
     */
    public function setArgs(array $args): void
    {
        if (count($args) !== 1 || !is_string($args[0])) {
            throw new InvalidArgumentException('Must pass a regex to compare against');
        }

        $this->regex = $args[0];
    }
}
