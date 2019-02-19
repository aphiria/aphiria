<h1>Console</h1>

[![Build Status](https://travis-ci.com/aphiria/console.svg)](https://travis-ci.com/aphiria/console)
[![Latest Stable Version](https://poser.pugx.org/aphiria/console/v/stable.svg)](https://packagist.org/packages/aphiria/console)
[![Latest Unstable Version](https://poser.pugx.org/aphiria/console/v/unstable.svg)](https://packagist.org/packages/aphiria/console)
[![License](https://poser.pugx.org/aphiria/console/license.svg)](https://packagist.org/packages/aphiria/console)

> **Note:** This library is still in development.

<h2>Table of Contents</h2>

1. [Introduction](#introduction)
2. [Running Commands](#running-commands)
3. [Getting Help](#getting-help)
4. [Creating Commands](#creating-commands)
  1. [Arguments](#arguments)
  2. [Options](#options)
    1. [Short Names](#short-names)
    2. [Long Names](#long-names)
    3. [Array Options](#array-options)
  3. [Creating Commands](#creating-commands)
    1. [Example](#example)
    2. [Registering Your Command](#registering-your-command)
  4. [Calling From Code](#calling-from-code)
  
<h2 id="introduction">Introduction</h2>

Console applications are great for administrative tasks and code generation.  With Aphiria, you can easily create your own console commands, display question prompts, and use HTML-like syntax for output styling.

<h2 id="running-commands">Running Commands</h2>

To run commands, type `php aphiria COMMAND_NAME` from the directory that Aphiria is installed in.

<h2 id="getting-help">Getting Help</h2>

To get help with any command, use the help command:

```
php aphiria help COMMAND_NAME
```

<h2 id="creating-commands">Creating Commands</h2>

<h3 id="arguments">Arguments</h3>

Console commands can accept arguments from the user.  Arguments can be required, optional, and/or arrays.  You specify the type by bitwise OR-ing the different arguments types.  Array arguments allow a variable number of arguments to be passed in, like "php aphiria foo arg1 arg2 arg3 ...".  The only catch is that array arguments must be the last argument defined for the command.

Let's take a look at an example argument:

```php
use Aphiria\Console\Input\Argument;
use Aphiria\Console\Input\ArgumentTypes;

// The argument will be required and an array
$type = ArgumentTypes::REQUIRED | ArgumentTypes::IS_ARRAY;
// The description argument is used by the help command
$argument = new Argument('foo', $type, 'The foo argument');
```

>**Note:** Like array arguments, optional arguments must appear after any required arguments.

<h3 id="options">Options</h3>

You might want different behavior in your command depending on whether or not an option is set.  This is possible using `Aphiria\Console\Input\Option`.  Options have two formats:

1. Short, eg "-h"
2. Long, eg "--help"

<h4 id="short-names">Short Names</h4>

Short option names are always a single letter.  Multiple short options can be grouped together.  For example, `-rf` means that options with short codes "r" and "f" have been specified.  The default value will be used for short options.

<h4 id="long-names">Long Names</h4>

Long option names can specify values in two ways:  `--foo=bar` or `--foo bar`.  If you only specify `--foo` for an optional-value option, then the default value will be used.

<h4 id="array-options">Array Options</h4>

Options can be arrays, eg `--foo=bar --foo=baz` will set the "foo" option to `["bar", "baz"]`.

Like arguments, option types can be specified by bitwise OR-ing types together.  Let's look at an example:

```php
use Aphiria\Console\Input\Option;
use Aphiria\Console\Input\OptionTypes;

$type = OptionTypes::IS_ARRAY | OptionTypes::REQUIRED_VALUE;
$option = new Option('foo', 'f', $types, 'The foo option');
```

<h3 id="creating-commands">Creating Commands</h3>

Creating a command is simple - create the command, associate it with a command handler, and register it.  The command defines the types of arguments and options a command takes, and a command handler actually processes input for that command.

Let's take a look at an example:

```php
namespace App\Application\Console\Commands;

use Aphiria\Console\Commands\{Command, CommandInput};
use Aphiria\Console\Input\{Argument, ArgumentTypes, Option, OptionTypes};
use Aphiria\Console\Output\IOutput;

$greetingCommand = new Command(
    'greet',
    [new Argument('name', ArgumentTypes::REQUIRED, 'The name to greet')],
    [new Option('yell', 'y', OptionTypes::OPTIONAL_VALUE, 'Yell the greeting?', 'yes')],
    'Greets a person'
);
$greetingCommandHandler = function (CommandInput $input, IOutput $output) {
    $greeting = 'Hello, ' . $input->arguments['name'];

    if ($input->options['yell'] === 'yes') {
        $greeting = strtoupper($greeting);
    }

    $output->writeln($greeting);
};
```

Your command handler can either be a `Closure` that takes the input and output as parameters, or it can implement `ICommandHandler`, which has a single method `handle()` that accepts the same parameters.  The following properties are available to you in `CommandInput`:

```php
$input->arguments['argName']; // The value of 'argName'
$input->options['optionName']; // The value of 'optionName'
```

<h4 id="registering-commands">Registering Commands</h4>

Before you can use your commands, you must register them so that the `Kernel` knows about them:

```php
use Aphiria\Console\Commands\CommandHandlerBinding;
use Aphiria\Console\Commands\CommandHandlerBindingRegistry;
use Aphiria\Console\Kernel;

$commandHandlers = new CommandHandlerBindingRegistry();
$commandHandlers->registerCommandHandlerBinding(
    new CommandHandlerBinding($greetingCommand, $greetingCommandHandler)
);

// Actually run the kernel
global $argv;
exit((new Kernel($commandHandlers))->handle($argv));
```

To call this command, run:

```
php aphiria greet Dave -y
```

This will output:

```
HELLO, DAVE
```

<h3 id="calling-from-code">Calling From Code</h3>

It's possible to call a command from another command by using `CommandHandlerBindingRegistry`:

```php
use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandHandlerBindingRegistry;
use Aphiria\Console\Commands\CommandInput;
use Aphiria\Console\Output\IOutput;

$commandHandler = function (CommandInput $input, IOutput $output) use ($commandHandlerBindings) {
    $fooHandler = $commandHandlerBindings->getCommandHandlerBinding('foo')->commandHandler;
    $fooHandler(new CommandInput(['arg1' => 'value'], ['option1' => 'value']), $output);
};
```

If you want to call the other command but not write its output, use the `Aphiria\Console\Output\SilentOutput` output.

> **Note:** If a command is being called by a lot of other commands, it might be best to refactor its actions into a separate class.  This way, it can be used by multiple commands without the extra overhead of calling console commands through PHP code.