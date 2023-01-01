<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
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
    /** @var array<string, true> The hash map of allowed methods */
    private array $allowedMethods = [];

    /**
     * @param list<string>|string $allowedMethods The list of allowed methods
     */
    public function __construct(string|array $allowedMethods)
    {
        foreach ((array)$allowedMethods as $allowedMethod) {
            $this->allowedMethods[\strtoupper($allowedMethod)] = true;
        }

        /**
         * Support HEAD requests wherever GET requests are supported
         *
         * @link https://www.w3.org/Protocols/rfc2616/rfc2616-sec5.html#sec5.1.1
         */
        if (isset($this->allowedMethods['GET'])) {
            $this->allowedMethods['HEAD'] = true;
        }
    }

    /**
     * Gets the list of allowed methods
     *
     * @return list<string> The list of allowed methods
     */
    public function getAllowedMethods(): array
    {
        return \array_keys($this->allowedMethods);
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
        return isset($this->allowedMethods[\strtoupper($httpMethod)]);
    }
}
