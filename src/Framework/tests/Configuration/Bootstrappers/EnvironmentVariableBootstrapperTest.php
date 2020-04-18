<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Configuration\Bootstrappers;

use Aphiria\Framework\Configuration\Bootstrappers\EnvironmentVariableBootstrapper;
use PHPUnit\Framework\TestCase;

class EnvironmentVariableBootstrapperTest extends TestCase
{
    private EnvironmentVariableBootstrapper $environmentVariableBootstrapper;

    protected function setUp(): void
    {
        $this->environmentVariableBootstrapper = new EnvironmentVariableBootstrapper(__DIR__ . '/files/.env.test');
    }

    public function testBootstrapMakesEnvironmentVariablesAccessible(): void
    {
        $this->environmentVariableBootstrapper->bootstrap();
        $this->assertEquals('bar', \getenv('FOO'));
    }
}
