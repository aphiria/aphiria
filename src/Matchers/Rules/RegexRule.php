<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers\Rules;

/**
 * Defines the regex rule
 */
class RegexRule implements IRule
{
    /** @var string The regex the input must match */
    private $regex;

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
        return preg_match($this->regex, $value) === 1;
    }
}
