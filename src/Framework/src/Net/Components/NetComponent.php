<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Net\Components;

use Aphiria\Application\IComponent;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Net\ExtensionMethods\BodyExtensionMethodRegistrant;
use Aphiria\Framework\Net\ExtensionMethods\HeaderExtensionMethodRegistrant;
use Aphiria\Framework\Net\ExtensionMethods\RequestExtensionMethodRegistrant;
use Aphiria\Framework\Net\ExtensionMethods\ResponseExtensionMethodRegistrant;
use Aphiria\Framework\Net\ExtensionMethods\UriExtensionMethodRegistrant;
use Aphiria\Net\Formatting\UriParser;
use Aphiria\Net\Http\Formatting\BodyParser;
use Aphiria\Net\Http\Formatting\HeaderParser;
use Aphiria\Net\Http\Formatting\RequestParser;
use Aphiria\Net\Http\Formatting\ResponseFormatter;

/**
 * Defines the Net library component
 */
class NetComponent implements IComponent
{
    /** @var bool Whether or not extension methods are enabled */
    private bool $extensionMethodsEnabled = false;

    /**
     * @param IContainer $container The DI container
     */
    public function __construct(private IContainer $container)
    {
    }

    /**
     * @inheritdoc
     */
    public function build(): void
    {
        if (!$this->extensionMethodsEnabled) {
            return;
        }

        // TODO: Where should I include phpstorm meta file about these extension methods?
        // TODO: Should I use PHPDoc in extended classes to indicate these extension methods?
        $requestParser = $responseFormatter = $uriParser = $headerParser = $bodyParser = null;
        $this->container->tryResolve(RequestParser::class, $requestParser);
        $this->container->tryResolve(ResponseFormatter::class, $responseFormatter);
        $this->container->tryResolve(UriParser::class, $uriParser);
        $this->container->tryResolve(HeaderParser::class, $headerParser);
        $this->container->tryResolve(BodyParser::class, $bodyParser);
        (new RequestExtensionMethodRegistrant($requestParser))->registerExtensionMethods();
        (new ResponseExtensionMethodRegistrant($responseFormatter))->registerExtensionMethods();
        (new UriExtensionMethodRegistrant($uriParser))->registerExtensionMethods();
        (new HeaderExtensionMethodRegistrant($headerParser))->registerExtensionMethods();
        (new BodyExtensionMethodRegistrant($bodyParser))->registerExtensionMethods();
    }

    /**
     * Enables built-in extension methods for select Net library classes
     *
     * @return static For chaining
     */
    public function withExtensionMethods(): static
    {
        $this->extensionMethodsEnabled = true;

        return $this;
    }
}
