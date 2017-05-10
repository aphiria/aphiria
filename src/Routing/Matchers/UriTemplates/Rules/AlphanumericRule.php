<?php
namespace Opulence\Routing\Matchers\UriTemplates\Rules;

/**
 * Defines the alphanumeric rule
 */
class AlphanumericRule implements IRule
{
    /**
     * @inheritdoc
     */
    public static function getSlug() : string
    {
        return 'alphanumeric';
    }

    /**
     * @inheritdoc
     */
    public function passes($value) : bool
    {
        return ctype_alnum($value) && strpos($value, ' ') === false;
    }
}
