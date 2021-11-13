<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Configuration\Bootstrappers;

use Aphiria\Application\IBootstrapper;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Defines the DotEnv bootstrapper
 */
final class DotEnvBootstrapper implements IBootstrapper
{
    /**
     * @param string $envPath The path to the .env file
     */
    public function __construct(private readonly string $envPath)
    {
    }

    /**
     * @inheritdoc
     */
    public function bootstrap(): void
    {
        $dotEnv = new Dotenv();
        $dotEnv->usePutenv()
            ->loadEnv($this->envPath);
    }
}
