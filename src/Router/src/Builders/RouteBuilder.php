<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Builders;

use Aphiria\Routing\Matchers\Constraints\HttpMethodRouteConstraint;
use Aphiria\Routing\Matchers\Constraints\IRouteConstraint;
use Aphiria\Routing\Middleware\MiddlewareBinding;
use Aphiria\Routing\Route;
use Aphiria\Routing\RouteAction;
use Aphiria\Routing\UriTemplates\UriTemplate;
use InvalidArgumentException;
use LogicException;

/**
 * Defines the route builder
 */
class RouteBuilder
{
    /** @var RouteAction|null ?RouteAction The action the route takes */
    private ?RouteAction $action = null;
    /** @var array<string, mixed> The mapping of custom route parameter names => values */
    private array $parameters = [];
    /** @var MiddlewareBinding[] The list of middleware bindings on this route */
    private array $middlewareBindings = [];
    /** @var string|null The name of this route */
    private ?string $name = null;
    /** @var IRouteConstraint[] The list of constraints */
    private array $constraints = [];

    /**
     * @param string[] $httpMethods The list of HTTP methods the route matches on
     * @param UriTemplate $uriTemplate The URI template the route matches on
     */
    public function __construct(array $httpMethods, private UriTemplate $uriTemplate)
    {
        $this->constraints[] = new HttpMethodRouteConstraint($httpMethods);
    }

    /**
     * Builds a route object from all the settings in this builder
     *
     * @return Route The built route
     * @throws LogicException Thrown if no controller was specified
     */
    public function build(): Route
    {
        if ($this->action === null) {
            throw new LogicException('No controller specified for route');
        }

        return new Route(
            $this->uriTemplate,
            $this->action,
            $this->constraints,
            $this->middlewareBindings,
            $this->name,
            $this->parameters
        );
    }

    /**
     * Binds the controller the route uses to be a method
     *
     * @param class-string $controllerClassName The name of the class the route goes to
     * @param string $controllerMethodName The name of the method the route goes to
     * @return static For chaining
     */
    public function mapsToMethod(string $controllerClassName, string $controllerMethodName): static
    {
        $this->action = new RouteAction($controllerClassName, $controllerMethodName);

        return $this;
    }

    /**
     * Binds a constraint to this route
     *
     * @param IRouteConstraint $constraint The constraint to add
     * @return static For chaining
     */
    public function withConstraint(IRouteConstraint $constraint): static
    {
        $this->constraints[] = $constraint;

        return $this;
    }

    /**
     * Binds constraints to this route
     *
     * @param IRouteConstraint[] $constraints The constraints to add
     * @return static For chaining
     */
    public function withManyConstraints(array $constraints): static
    {
        $this->constraints = [...$this->constraints, ...$constraints];

        return $this;
    }

    /**
     * Binds many middleware bindings to the route
     *
     * @param array<MiddlewareBinding|class-string|string> $middlewareBindings The list of middleware bindings to add, or a single
     *      class name without properties
     * @return static For chaining
     * @throws InvalidArgumentException Thrown if the middleware bindings are not the correct type
     * @psalm-suppress RedundantConditionGivenDocblockType We need to check the type to get code coverage
     */
    public function withManyMiddleware(array $middlewareBindings): static
    {
        foreach ($middlewareBindings as $middlewareBinding) {
            if (\is_string($middlewareBinding)) {
                /** @psalm-suppress ArgumentTypeCoercion Psalm is being tripped up by the fact this is a string, not a class-string - bug */
                $this->middlewareBindings[] = new MiddlewareBinding($middlewareBinding);
            } elseif ($middlewareBinding instanceof MiddlewareBinding) {
                $this->middlewareBindings[] = $middlewareBinding;
            } else {
                throw new InvalidArgumentException(
                    'Middleware binding must either be a string or an instance of ' . MiddlewareBinding::class
                );
            }
        }

        return $this;
    }

    /**
     * Binds many custom parameters to the route
     * This is useful for custom route constraint matching
     *
     * @param array<string, mixed> $parameters The mapping of custom parameter names => values
     * @return static For chaining
     */
    public function withManyParameters(array $parameters): static
    {
        $this->parameters = \array_merge($this->parameters, $parameters);

        return $this;
    }

    /**
     * Binds a single middleware class to the route
     *
     * @param class-string $middlewareClassName The name of the middleware class to bind
     * @param array<string, mixed> $middlewareParameters Any parameters this method relies on
     * @return static For chaining
     */
    public function withMiddleware(string $middlewareClassName, array $middlewareParameters = []): static
    {
        $this->middlewareBindings[] = new MiddlewareBinding($middlewareClassName, $middlewareParameters);

        return $this;
    }

    /**
     * Binds a name to the route
     *
     * @param string $name The name of the route
     * @return static For chaining
     */
    public function withName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Binds a custom parameter to the route
     * This is useful for custom route constraint matching
     *
     * @param string $name The name of the parameter
     * @param mixed $value The value of the parameter
     * @return static For chaining
     */
    public function withParameter(string $name, mixed $value): static
    {
        $this->parameters[$name] = $value;

        return $this;
    }
}
