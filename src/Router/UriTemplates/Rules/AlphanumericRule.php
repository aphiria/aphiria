<?php
namespace Opulence\Router\UriTemplates\Rules;

/**
 * Defines the alphanumeric rule
 */
class AlphanumericRule implements IRule
{
    /**
     * @inheritdoc
     */
    public function getSlug() : string
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
