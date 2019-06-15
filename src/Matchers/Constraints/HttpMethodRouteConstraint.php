<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Matchers\Constraints;

use Aphiria\Routing\Matchers\MatchedRouteCandidate;
use InvalidArgumentException;

/**
 * Defines the HTTP method route constraint
 */
final class HttpMethodRouteConstraint implements IRouteConstraint
{
    /** @var array The hash map of allowed methods */
    private array $allowedMethods = [];

    /**
     * @param array|string $allowedMethods The list of allowed methods
     * @throws InvalidArgumentException Thrown if the input methods are not a string or an array of strings
     */
    public function __construct($allowedMethods)
    {
        if (\is_string($allowedMethods)) {
            $this->allowedMethods[\strtoupper($allowedMethods)] = true;
        } elseif (\is_array($allowedMethods)) {
            foreach ($allowedMethods as $allowedMethod) {
                $this->allowedMethods[\strtoupper($allowedMethod)] = true;
            }
        } else {
            throw new InvalidArgumentException('Allowed methods must be a string or array of strings');
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
     * @return array The list of allowed methods
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
