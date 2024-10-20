<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Routing\Commands;

use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\Formatters\PaddingFormatter;
use Aphiria\Console\Output\IOutput;
use Aphiria\Console\StatusCode;
use Aphiria\Middleware\MiddlewareCollection;
use Aphiria\Routing\Matchers\Constraints\HttpMethodRouteConstraint;
use Aphiria\Routing\Route;
use Aphiria\Routing\RouteCollection;
use RuntimeException;

/**
 * Defines the route list command handler
 */
class RouteListCommandHandler implements ICommandHandler
{
    /**
     * @param RouteCollection $routes The list of routes registered to the application
     * @param MiddlewareCollection $middleware The global list of middleware
     * @param PaddingFormatter $paddingFormatter The padding formatter to use when outputting the routes
     */
    public function __construct(
        private readonly RouteCollection $routes,
        private readonly MiddlewareCollection $middleware,
        private readonly PaddingFormatter $paddingFormatter = new PaddingFormatter()
    ) {
    }

    /**
     * @inheritdoc
     * @throws RuntimeException Thrown if any of the routes did not have an HTTP method constraint
     */
    public function handle(Input $input, IOutput $output)
    {
        $useFqn = \array_key_exists('fqn', $input->options);
        $showMiddleware = isset($input->options['middleware']);
        /** @var array $middlewareOptions */
        $middlewareOptions = $input->options['middleware'] ?? [];
        $sortedRoutes = $this->routes->values;
        \usort($sortedRoutes, fn (Route $routeA, Route $routeB): int => $this->compareRoutes($routeA, $routeB));
        $rows = [['<b>Method</b>', '<b>Path</b>', '<b>Action</b>']];

        foreach ($sortedRoutes as $route) {
            $formattedMiddlewareText = '';

            if ($showMiddleware) {
                $formattedMiddlewareClassNames = [];

                if (\in_array('global', $middlewareOptions)) {
                    foreach ($this->middleware->values as $middleware) {
                        $formattedMiddlewareClassNames[] = "<comment>{$this->formatClassName($middleware::class, $useFqn)}</comment>";
                    }
                }

                foreach ($route->middlewareBindings as $middlewareBinding) {
                    $formattedMiddlewareClassNames[] = "<comment>{$this->formatClassName($middlewareBinding->className, $useFqn)}</comment>";
                }

                if (\count($formattedMiddlewareClassNames) > 0) {
                    $formattedMiddlewareText = \implode(' → ', $formattedMiddlewareClassNames) . ' → ';
                }
            }

            $rows[] = [
                $this->formatHttpMethodString($route),
                $this->formatUriTemplate($route->uriTemplate->pathTemplate),
                "$formattedMiddlewareText<comment>{$this->formatClassName($route->action->className, $useFqn)}::{$route->action->methodName}</comment>"
            ];
        }

        /** @psalm-suppress MixedArgumentTypeCoercion The row parameter will be implode-able */
        $output->writeln($this->paddingFormatter->format($rows, fn (array $row): string => \implode('    ', $row)));

        return StatusCode::Ok;
    }

    /**
     * Compares two routes for sorting
     *
     * @param Route $routeA The first route
     * @param Route $routeB The second route
     * @return int -1|0|1 The result of comparison
     */
    private function compareRoutes(Route $routeA, Route $routeB): int
    {
        // If the paths are the same, sort by the HTTP methods
        if ($routeA->uriTemplate->pathTemplate === $routeB->uriTemplate->pathTemplate) {
            return \strcasecmp($this->formatHttpMethodString($routeA), $this->formatHttpMethodString($routeB));
        }

        return \strcasecmp($routeA->uriTemplate->pathTemplate, $routeB->uriTemplate->pathTemplate);
    }

    /**
     * Formats a class name for output
     *
     * @param class-string $className The name of the class to format
     * @param bool $useFullyQualifiedName Whether or not to use the fully qualified class name
     * @return string The formatted class name
     */
    private function formatClassName(string $className, bool $useFullyQualifiedName): string
    {
        if ($useFullyQualifiedName) {
            return $className;
        }

        $lastSlashIndex = \strrpos($className, '\\');

        return \substr($className, $lastSlashIndex === false ? 0 : $lastSlashIndex + 1);
    }

    /**
     * Formats the HTTP methods of a route to an output-able string
     *
     * @param Route $route The route whose HTTP methods we want to format
     * @return string The formatted HTTP method string
     * @throws RuntimeException Thrown if the route didn't have an HTTP method constraint
     */
    private function formatHttpMethodString(Route $route): string
    {
        $httpMethods = null;

        foreach ($route->constraints as $constraint) {
            if ($constraint instanceof HttpMethodRouteConstraint) {
                $httpMethods = $constraint->allowedMethods;
                break;
            }
        }

        if ($httpMethods === null) {
            throw new RuntimeException('No ' . HttpMethodRouteConstraint::class . ' constraint registered for route with path template "' . $route->uriTemplate->pathTemplate . '"');
        }

        \sort($httpMethods);

        return \implode('|', $httpMethods);
    }

    /**
     * Formats a URI template
     *
     * @param string $uriTemplate The URI template to format
     * @return string The formatted URI template
     */
    private function formatUriTemplate(string $uriTemplate): string
    {
        return \preg_replace('/\/(:[^\/$\[\]]+)/', '/<info>\1</info>', $uriTemplate);
    }
}
