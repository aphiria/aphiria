<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Matchers\Constraints;

use Aphiria\Routing\Matchers\MatchedRouteCandidate;

/**
 * Defines the HTTP method route constraint
 */
final class HttpMethodRouteConstraint implements IRouteConstraint
{
    /** @var list<string> The list of allowed methods */
    public array $allowedMethods {
        get => \array_keys($this->_allowedMethods);
    }
    /** @var array<string, true> The hash map of allowed methods */
    private array $_allowedMethods = [];

    /**
     * @param list<string>|string $allowedMethods The list of allowed methods
     */
    public function __construct(string|array $allowedMethods)
    {
        foreach ((array)$allowedMethods as $allowedMethod) {
            $this->_allowedMethods[\strtoupper($allowedMethod)] = true;
        }

        /**
         * Support HEAD requests wherever GET requests are supported
         *
         * @link https://www.w3.org/Protocols/rfc2616/rfc2616-sec5.html#sec5.1.1
         */
        if (isset($this->_allowedMethods['GET'])) {
            $this->_allowedMethods['HEAD'] = true;
        }
    }

    /**
     * @inheritdoc
     */
    public function passes(
        MatchedRouteCandidate $matchedRouteCandidate,
        string $httpMethod,
        string $host,
        string $path,
        array $headers
    ): bool {
        return isset($this->_allowedMethods[\strtoupper($httpMethod)]);
    }
}
