<?php
namespace Opulence\Router\UriTemplates\Rules;

/**
 * Defines the alpha rule
 */
class AlphaRule implements IRule
{
    /**
     * @inheritdoc
     */
    public function getSlug() : string
    {
        return 'alpha';
    }

    /**
     * @inheritdoc
     */
    public function passes($value) : bool
    {
        return ctype_alpha($value) && strpos($value, ' ') === false;
    }
}
