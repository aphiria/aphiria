<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Matchers\Rules;

/**
 * Defines the regex rule
 */
final class RegexRule implements IRule
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
     * @inheritdoc
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
