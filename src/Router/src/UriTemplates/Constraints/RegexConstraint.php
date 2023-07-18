<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Constraints;

use InvalidArgumentException;

/**
 * Defines the regex constraint
 */
final class RegexConstraint implements IRouteVariableConstraint
{
    /** @var non-empty-string The regex the input must match */
    private readonly string $regex;

    /**
     * @param string $regex The regex the input must match
     * @throws InvalidArgumentException Thrown if the regex was empty
     */
    public function __construct(string $regex)
    {
        if (empty($regex)) {
            throw new InvalidArgumentException('Regex cannot be empty');
        }

        $this->regex = $regex;
    }

    /**
     * Gets the slug that will be used to actually add a constraint in a URI template
     *
     * @return string The slug used in the URI template
     */
    public static function getSlug(): string
    {
        return 'regex';
    }

    /**
     * @inheritdoc
     */
    public function passes(mixed $value): bool
    {
        return \preg_match($this->regex, (string)$value) === 1;
    }
}
