<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

namespace Aphiria\Routing\UriTemplates;

/**
 * Defines a URI template
 */
final class UriTemplate
{
    /** @var string|null The host template */
    public $hostTemplate;
    /** @var string|null The path template */
    public $pathTemplate;
    /** @var bool Whether or not this URI template is https-only */
    public $isHttpsOnly;
    /** @var bool Whether or not the URI is absolute */
    public $isAbsoluteUri;

    /**
     * @param string $pathTemplate The path template
     * @param string|null $hostTemplate The host template, or null if there is none
     * @param bool $isHttpsOnly Whether or not this URI template is https-only
     */
    public function __construct(string $pathTemplate, string $hostTemplate = null, bool $isHttpsOnly = true)
    {
        $this->pathTemplate = '/' . ltrim($pathTemplate, '/');
        $this->hostTemplate = $hostTemplate === null ? null : rtrim($hostTemplate, '/');
        $this->isHttpsOnly = $isHttpsOnly;
        $this->isAbsoluteUri = $this->hostTemplate !== null;
    }

    /**
     * Serializes this template to a string
     *
     * @return string The URI template string
     */
    public function __toString(): string
    {
        return ($this->hostTemplate ?? '') . $this->pathTemplate;
    }
}