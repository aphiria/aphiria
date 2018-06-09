<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\UriTemplates\Compilers;

use InvalidArgumentException;
use Opulence\Routing\UriTemplates\UriTemplate;

/**
 * Defines the interface for URI template compilers to implement
 */
interface IUriTemplateCompiler
{
    /**
     * Compiles a raw URI template
     *
     * @param string|null $hostTemplate The raw host template
     * @param string $pathTemplate The raw path template
     * @param bool $isHttpsOnly Whether or not the route is HTTPS-only
     * @return UriTemplate The compiled URI template
     * @throws InvalidArgumentException Thrown if the template is invalid
     */
    public function compile(?string $hostTemplate, string $pathTemplate, bool $isHttpsOnly = false): UriTemplate;
}
