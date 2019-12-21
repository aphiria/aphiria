<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Constraints;

/**
 * Defines the regex constraint
 */
final class RegexConstraint implements IRouteVariableConstraint
{
    /** @var string The regex the input must match */
    private string $regex;

    /**
     * @param string $regex The regex the input must match
     */
    public function __construct(string $regex)
    {
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
    public function passes($value): bool
    {
        return \preg_match($this->regex, $value) === 1;
    }
}
