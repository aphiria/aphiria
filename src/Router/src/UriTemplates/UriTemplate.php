<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates;

/**
 * Defines a URI template
 */
final class UriTemplate
{
    /** @var bool Whether or not the URI is absolute */
    public bool $isAbsoluteUri;

    /**
     * @param string $pathTemplate The path template
     * @param string|null $hostTemplate The host template, or null if there is none
     * @param bool $isHttpsOnly Whether or not this URI template is https-only
     */
    public function __construct(
        public string $pathTemplate,
        public ?string $hostTemplate = null,
        public bool $isHttpsOnly = true
    ) {
        $this->pathTemplate = '/' . \ltrim($pathTemplate, '/');
        $this->hostTemplate = $hostTemplate === null ? null : \rtrim($hostTemplate, '/');
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
