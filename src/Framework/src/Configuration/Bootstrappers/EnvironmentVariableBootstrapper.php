<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Configuration\Bootstrappers;

use Aphiria\Application\IBootstrapper;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Defines the environment variable bootstrapper
 */
final class EnvironmentVariableBootstrapper implements IBootstrapper
{
    /** @var string The path to the .env file */
    private string $envPath;

    /**
     * @param string $envPath The path to the .env file
     */
    public function __construct(string $envPath)
    {
        $this->envPath = $envPath;
    }

    /**
     * @inheritdoc
     */
    public function bootstrap(): void
    {
        // We'll also write environment variables using putenv()
        (new Dotenv(true))->loadEnv($this->envPath);
    }
}
