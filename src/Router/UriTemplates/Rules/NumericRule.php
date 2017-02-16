<?php
namespace Opulence\Router\UriTemplates\Rules;

/**
 * Defines the numeric rule
 */
class NumericRule implements IRule
{
    /**
     * @inheritdoc
     */
    public function getSlug() : string
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
