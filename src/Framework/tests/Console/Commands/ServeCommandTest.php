<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Console\Commands;

use Aphiria\Application\Configuration\GlobalConfiguration;
use Aphiria\Application\Configuration\HashTableConfiguration;
use Aphiria\Console\Input\Option;
use Aphiria\Console\Input\OptionType;
use Aphiria\Framework\Console\Commands\ServeCommand;
use PHPUnit\Framework\TestCase;

class ServeCommandTest extends TestCase
{
    protected function setUp(): void
    {
        GlobalConfiguration::resetConfigurationSources();
    }

    public function testCorrectValuesAreSetInConstructor(): void
    {
        $command = new ServeCommand('router');
        $this->assertSame('app:serve', $command->name);
        $this->assertSame('Runs your app locally', $command->description);
        $expectedOptions = [
            new Option('domain', OptionType::RequiredValue, null, 'The domain to run your app at', 'localhost'),
            new Option('port', OptionType::RequiredValue, null, 'The port to run your app at', 8080),
            new Option('docroot', OptionType::RequiredValue, null, 'The document root of your app', 'public'),
            new Option('router', OptionType::RequiredValue, null, 'The router file for your app', 'router')
        ];
        $this->assertEquals($expectedOptions, $command->options);
    }

    public function testRouterPathCanBeSetFromConfig(): void
    {
        GlobalConfiguration::addConfigurationSource(
            new HashTableConfiguration(['aphiria' => ['api' => ['localhostRouterPath' => '/foo']]])
        );
        $command = new ServeCommand();
        $this->assertCount(4, $command->options);
        $this->assertSame('/foo', $command->options[3]->defaultValue);
    }
}
