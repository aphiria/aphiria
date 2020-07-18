<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Tests\Configuration\Bootstrappers;

use Aphiria\Application\Configuration\Bootstrappers\DotEnvBootstrapper;
use PHPUnit\Framework\TestCase;

class DotEnvBootstrapperTest extends TestCase
{
    private DotEnvBootstrapper $dotEnvBootstrapper;

    protected function setUp(): void
    {
        $this->dotEnvBootstrapper = new DotEnvBootstrapper(__DIR__ . '/files/.env.test');
    }

    public function testBootstrapReadsDotEnvFilesIntoEnvironmentVariables(): void
    {
        $this->dotEnvBootstrapper->bootstrap();
        $this->assertEquals('bar', \getenv('FOO'));
    }
}
