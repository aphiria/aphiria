<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Authentication\Components;

use Aphiria\Application\IComponent;
use Aphiria\Authentication\AuthenticationScheme;
use Aphiria\Authentication\AuthenticationSchemeOptions;
use Aphiria\Authentication\AuthenticationSchemeRegistry;
use Aphiria\DependencyInjection\IServiceResolver;

/**
 * Defines the authentication component
 */
class AuthenticationComponent implements IComponent
{
    /** @var list<array{0: AuthenticationScheme<AuthenticationSchemeOptions>, 1: bool}> */
    private array $schemeConfigs = [];

    /**
     * @param IServiceResolver $serviceResolver The service resolver
     */
    public function __construct(private readonly IServiceResolver $serviceResolver)
    {
    }

    /**
     * @inheritdoc
     */
    public function build(): void
    {
        $schemes = $this->serviceResolver->resolve(AuthenticationSchemeRegistry::class);

        foreach ($this->schemeConfigs as $schemeConfig) {
            $schemes->registerScheme($schemeConfig[0], $schemeConfig[1]);
        }
    }

    /**
     * Adds an authentication scheme to the authenticator
     *
     * @template T of AuthenticationSchemeOptions
     * @param AuthenticationScheme<T> $scheme The scheme to register
     * @param bool $isDefault Whether or not the scheme is the default scheme
     * @return static For chaining
     */
    public function withScheme(AuthenticationScheme $scheme, bool $isDefault = false): static
    {
        $this->schemeConfigs[] = [$scheme, $isDefault];

        return $this;
    }
}
