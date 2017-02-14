<?php
namespace Opulence\Router\UriTemplates\Rules;

/**
 * Defines the integer rule
 */
class IntegerRule implements IRule
{
    /**
     * @inheritdoc
     */
    public function getSlug() : string
    {
        return 'int';
    }
    
    /**
     * @inheritdoc
     */
    public function passes($value) : bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
}
