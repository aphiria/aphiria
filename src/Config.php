<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Configuration;

use Opulence\Api\Exceptions\IExceptionHandler;
use Opulence\Ioc\IContainer;
use Opulence\Net\Http\ContentNegotiation\IContentNegotiator;
use Opulence\Routing\Matchers\IRouteMatcher;
use Psr\Log\LoggerInterface;

/**
 * Defines an application configuration
 */
class Config
{
    /** @var array The mapping of slugs to paths */
    public $paths = [];
    /** @var IExceptionHandler The exception handler */
    public $exceptionHandler;
    /** @var LoggerInterface The logger */
    public $logger;
    /** @var IContainer The DI container */
    public $container;
    /** @var IRouteMatcher The route matcher */
    public $routeMatcher;
    /** @var IContentNegotiator The content negotiator */
    public $contentNegotiator;
    /** @var array The mapping of category names to settings */
    private $settings = [];

    /**
     * @param array $paths The mapping of slugs to paths
     * @param IExceptionHandler $exceptionHandler The exception handler
     * @param LoggerInterface $logger The logger
     * @param IContainer $container The DI container
     * @param IRouteMatcher $routeMatcher The route matcher
     * @param IContentNegotiator $contentNegotiator The content negotiator
     */
    public function __construct(
        array $paths,
        IExceptionHandler $exceptionHandler,
        LoggerInterface $logger,
        IContainer $container,
        IRouteMatcher $routeMatcher,
        IContentNegotiator $contentNegotiator
    ) {
        $this->paths = $paths;
        $this->exceptionHandler = $exceptionHandler;
        $this->logger = $logger;
        $this->container = $container;
        $this->routeMatcher = $routeMatcher;
        $this->contentNegotiator = $contentNegotiator;
    }

    /**
     * Gets a setting
     *
     * @param string $category The category of setting to get
     * @param string $setting The name of the setting to get
     * @param mixed $default The default value if one does not exist
     * @return mixed The value of the setting
     */
    public function get(string $category, string $setting, $default = null)
    {
        if (!isset($this->settings[$category][$setting])) {
            return $default;
        }

        return $this->settings[$category][$setting];
    }

    /**
     * Gets whether or not a setting has a value
     *
     * @param string $category The category whose setting we're checking
     * @param string $setting The setting to check for
     * @return bool True if the setting exists, otherwise false
     */
    public function has(string $category, string $setting): bool
    {
        return isset($this->settings[$category][$setting]);
    }

    /**
     * Sets a setting
     *
     * @param string $category The category whose setting we're changing
     * @param string $setting The name of the setting to set
     * @param mixed $value The value of the setting
     */
    public function set(string $category, string $setting, $value): void
    {
        if (!isset($this->settings[$category])) {
            $this->settings[$category] = [];
        }

        $this->settings[$category][$setting] = $value;
    }
    
    /**
     * Sets an entire category's settings (overwrites previous settings)
     *
     * @param string $category The category whose settings we're changing
     * @param array $settings The array of settings
     */
    public function setCategory(string $category, array $settings): void
    {
        $this->settings[$category] = $settings;
    }
}