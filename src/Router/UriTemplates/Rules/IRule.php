<?php
namespace Opulence\Router\UriTemplates\Rules;

/**
 * Defines the interface for URI template rules to implement
 */
interface IRule
{
    /**
     * Gets the slug (short-name) for the rule
     *
     * @return string The slug
     */
    public function getSlug() : string;

    /**
     * Gets whether or not the rule passes
     * 
     * @param mixed $value The value to validate
     * @return bool True if the rule passes, otherwise false
     */
    public function passes($value) : bool;
}
