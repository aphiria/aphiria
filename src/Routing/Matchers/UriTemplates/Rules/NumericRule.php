<?php
namespace Opulence\Routing\Matchers\UriTemplates\Rules;

/**
 * Defines the numeric rule
 */
class NumericRule implements IRule
{
    /**
     * @inheritdoc
     */
    public static function getSlug() : string
    {
        return 'numeric';
    }

    /**
     * @inheritdoc
     */
    public function passes($value) : bool
    {
        return is_numeric($value);
    }
}
