<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Console\Commands;

use Aphiria\Configuration\GlobalConfiguration;
use Aphiria\Configuration\HashTableConfiguration;
use Aphiria\Console\Input\Option;
use Aphiria\Console\Input\OptionTypes;
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
        $this->assertEquals('app:serve', $command->name);
        $this->assertEquals('Runs your app locally', $command->description);
        $expectedOptions = [
            new Option('domain', null, OptionTypes::REQUIRED_VALUE, 'The domain to run your app at', 'localhost'),
            new Option('port', null, OptionTypes::REQUIRED_VALUE, 'The port to run your app at', 80),
            new Option('docroot', null, OptionTypes::REQUIRED_VALUE, 'The document root of your app', 'public'),
            new Option('router', null, OptionTypes::REQUIRED_VALUE, 'The router file for your app', 'router')
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
        $this->assertEquals('/foo', $command->options[3]->defaultValue);
    }
}
