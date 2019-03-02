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
4. [Arguments](#arguments)
5. [Options](#options)
    1. [Short Names](#short-names)
    2. [Long Names](#long-names)
    3. [Array Options](#array-options)
6. [Creating Commands](#creating-commands)
    1. [Calling From Code](#calling-from-code)
7. [Prompts](#prompts)
    1. [Confirmation](#confirmation)
    2. [Multiple Choice](#multiple-choice)
8. [Output](#output)
9. [Formatters](#formatters)
    1. [Padding](#padding)
    2. [Tables](#tables)
10. [Style Elements](#style-elements)
    1. [Built-In Elements](#built-in-elements)
    2. [Custom Elements](#custom-elements)
    3. [Overriding Built-In Elements](#overriding-built-in-elements)
  
<h2 id="introduction">Introduction</h2>

Console applications are great for administrative tasks and code generation.  With Aphiria, you can easily create your own console commands, display question prompts, and use HTML-like syntax for output styling.

<h2 id="running-commands">Running Commands</h2>

To run commands, type `php aphiria COMMAND_NAME` from the directory that Aphiria is installed in.

<h2 id="getting-help">Getting Help</h2>

To get help with any command, use the help command:

```
php aphiria help COMMAND_NAME
```

<h2 id="arguments">Arguments</h2>

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

<h2 id="options">Options</h2>

You might want different behavior in your command depending on whether or not an option is set.  This is possible using `Aphiria\Console\Input\Option`.  Options have two formats:

1. Short, eg "-h"
2. Long, eg "--help"

<h3 id="short-names">Short Names</h3>

Short option names are always a single letter.  Multiple short options can be grouped together.  For example, `-rf` means that options with short codes "r" and "f" have been specified.  The default value will be used for short options.

<h3 id="long-names">Long Names</h3>

Long option names can specify values in two ways:  `--foo=bar` or `--foo bar`.  If you only specify `--foo` for an optional-value option, then the default value will be used.

<h3 id="array-options">Array Options</h3>

Options can be arrays, eg `--foo=bar --foo=baz` will set the "foo" option to `["bar", "baz"]`.

Like arguments, option types can be specified by bitwise OR-ing types together.  Let's look at an example:

```php
use Aphiria\Console\Input\Option;
use Aphiria\Console\Input\OptionTypes;

$type = OptionTypes::IS_ARRAY | OptionTypes::REQUIRED_VALUE;
$option = new Option('foo', 'f', $types, 'The foo option');
```

<h2 id="creating-commands">Creating Commands</h2>

Creating a command is simple - create the command, associate it with a command handler, and register it.  The command defines the types of arguments and options a command takes, and a command handler actually processes input for that command.

Let's take a look at an example:

```php
namespace App\Application\Console\Commands;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Input\{Argument, ArgumentTypes, Input, Option, OptionTypes};
use Aphiria\Console\Output\IOutput;

$greetingCommand = new Command(
    'greet',
    [new Argument('name', ArgumentTypes::REQUIRED, 'The name to greet')],
    [new Option('yell', 'y', OptionTypes::OPTIONAL_VALUE, 'Yell the greeting?', 'yes')],
    'Greets a person'
);
$greetingCommandHandler = function (Input $input, IOutput $output) {
    $greeting = 'Hello, ' . $input->arguments['name'];

    if ($input->options['yell'] === 'yes') {
        $greeting = strtoupper($greeting);
    }

    $output->writeln($greeting);
};
```

Your command handler can either be a `Closure` that takes the input and output as parameters, or it can implement `ICommandHandler`, which has a single method `handle()` that accepts the same parameters.  If you pass in a `Closure`, it will be wrapped in a `ClosureCommandHandler`.  The following properties are available to you in `Input`:

```php
$input->commandName; // The name of the command that was invoked
$input->arguments['argName']; // The value of 'argName'
$input->options['optionName']; // The value of 'optionName'
```

<h3 id="registering-commands">Registering Commands</h3>

Before you can use your commands, you must register them so that the `Kernel` knows about them:

```php
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Kernel;

$commands = new CommandRegistry();
$commands->registerCommand($greetingCommand, $greetingCommandHandler);

// Actually run the kernel
global $argv;
exit((new Kernel($commands))->handle($argv));
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

It's possible to call a command from another command by using `Kernel`:

```php
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;

$commandHandler = function (Input $input, IOutput $output) use ($kernel) {
    $kernel->handle('foo arg1 --option1=value', $output);
    
    // Do other stuff...
};

// Register your commands...
```

Alternatively, if your handler is a class, you could inject the kernel via the constructor:

```php
use Aphiria\Console\Commands\ICommandBus;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;

class FooCommandHandler implements ICommandHandler
{
    private $kernel;
    
    public function __construct(ICommandBus $kernel)
    {
        $this->kernel = $kernel;
    }
    
    public function handle(Input $input, IOutput $output)
    {
        $this->kernel->handle('foo arg1 --option1=value', $output);
    
        // Do other stuff...
    };
}
```

If you want to call the other command but not write its output, use the `Aphiria\Console\Output\SilentOutput` output.

> **Note:** If a command is being called by a lot of other commands, it might be best to refactor its actions into a separate class.  This way, it can be used by multiple commands without the extra overhead of calling console commands through PHP code.

<h2 id="prompts">Prompts</h2>

Prompts are great for asking users for input beyond what is accepted by arguments.  For example, you might want to confirm with a user before doing an administrative task, or you might ask her to select from a list of possible choices.  Prompts accept `Aphiria\Console\Output\Prompts\Question` objects.

<h4 id="confirmation">Confirmation</h4>

To ask a user to confirm an action with a simple "y" or "yes", use an `Aphiria\Console\Output\Prompts\Confirmation`:

```php
use Aphiria\Console\Output\Prompts\Prompt;
use Aphiria\Console\Output\Prompts\Confirmation;

$prompt = new Prompt();
// This will return true if the answer began with "y" or "Y"
$prompt->ask(new Confirmation('Are you sure you want to continue?'), $output);
```

<h4 id="multiple-choice">Multiple Choice</h4>

Multiple choice questions are great for listing choices that might otherwise be difficult for a user to remember.  An `Aphiria\Console\Output\Prompts\MultipleChoice` accepts question text and a list of choices:

```php
use Aphiria\Console\Output\Prompts\MultipleChoice;

$choices = ['Boeing 747', 'Boeing 757', 'Boeing 787'];
$question = new MultipleChoice('Select your favorite airplane', $choices);
$prompt->ask($question, $output);
```

This will display:

```php
Select your favorite airplane
  1) Boeing 747
  2) Boeing 757
  3) Boeing 787
  >
```

If the `$choices` array is associative, then the keys will map to values rather than 1)...N).

<h2 id="output">Output</h2>

Outputs allow you to write messages to an end user.  The different outputs include:

1. `Aphiria\Console\Output\ConsoleOutput`
    * Used to write messages to the console
    * The output used by default
2. `Aphiria\Console\Output\SilentOutput`
    * Used when we don't want any messages to be written
    * Useful for when one command calls another

Each output offers three methods:

1. `readLine()`
    * Reads a line of input
1. `write()`
    * Writes a message to the existing line
2. `writeln()`
    * Writes a message to a new line
3. `clear()`
    * Clears the current screen
    * Only works in `ConsoleOutput`

<h2 id="formatters">Formatters</h2>

Formatters are great for nicely-formatting output to the console.

<h4 id="padding">Padding</h4>

The `Aphiria\Console\Output\Formatters\PaddingFormatter` formatter allows you to create column-like output.  It accepts an array of column values.  The second parameter is a callback that will format each row's contents.  Let's look at an example:

```php
use Aphiria\Console\Output\Formatters\PaddingFormatter;

$paddingFormatter = new PaddingFormatter();
$rows = [
    ['George', 'Carlin', 'great'],
    ['Chris', 'Rock', 'good'],
    ['Jim', 'Gaffigan', 'pale']
];
$paddingFormatter->format($rows, function ($row) {
    return $row[0] . ' - ' . $row[1] . ' - ' . $row[2];
});
```

This will return:
```
George - Carlin   - great
Chris  - Rock     - good
Jim    - Gaffigan - pale
```

There are a few useful functions for customizing the padding formatter:

* `setEolChar()`
    * Sets the end-of-line character
* `setPadAfter()`
    * Sets whether to pad before or after strings
* `setPaddingString()`
    * Sets the padding string

<h4 id="tables">Tables</h4>

ASCII tables are a great way to show tabular data in a console.  To create a table, use `Aphiria\Console\Output\Formatters\TableFormatter`:

```php
use Aphiria\Console\Output\Formatters\TableFormatter;

$table = new TableFormatter();
$rows = [
    ['Sean', 'Connery'],
    ['Pierce', 'Brosnan']
];
$table->format($rows);
```

This will return:

```
+--------+---------+
| Sean   | Connery |
| Pierce | Brosnan |
+--------+---------+
```

Headers can also be included in tables:

```php
$headers = ['First', 'Last'];
$table->format($rows, $headers);
```

This will return:

```
+--------+---------+
| First  | Last    |
+--------+---------+
| Sean   | Connery |
| Pierce | Brosnan |
+--------+---------+
```

There are a few useful functions for customizing the look of tables:

* `setCellPaddingString()`
    * Sets the cell padding string
* `setEolChar()`
    * Sets the end-of-line character
* `setHorizontalBorderChar()`
    * Sets the horizontal border character
* `setIntersectionChar()`
    * Sets the row/column intersection character
* `setPadAfter()`
    * Sets whether to pad before or after strings
* `setVerticalBorderChar()`
    * Sets the vertical border character

<h2 id="style-elements">Style Elements</h2>

Aphiria supports HTML-like style elements to perform basic output formatting like background color, foreground color, boldening, and underlining.  For example, writing:

```
<b>Hello!</b>
```

...will output "<b>Hello!</b>".  You can even nest elements:

```
<u>Hello, <b>Dave</b></u>
```

..., which will output an underlined string where "Dave" is both bold AND underlined.

<h4 id="built-in-elements">Built-In Elements</h4>

The following elements come built-into Aphiria:
* &lt;success&gt;&lt;/success&gt;
* &lt;info&gt;&lt;/info&gt;
* &lt;question&gt;&lt;/question&gt;
* &lt;comment&gt;&lt;/comment&gt;
* &lt;error&gt;&lt;/error&gt;
* &lt;fatal&gt;&lt;/fatal&gt;
* &lt;b&gt;&lt;/b&gt;
* &lt;u&gt;&lt;/u&gt;

<h4 id="custom-elements">Custom Elements</h4>

You can create your own style elements.  Elements are registered to `Aphiria\Console\Output\Compilers\Elements\ElementRegistry`.

```php
use Aphiria\Console\Output\Compilers\Elements\{Colors, Element, ElementRegistry, Style, TextStyles};
use Aphiria\Console\Output\Compilers\OutputCompiler;
use Aphiria\Console\Output\ConsoleOutput;

$elements = new ElementRegistry();
$elements->registerElement(
    new Element('foo', new Style(Colors::BLACK, Colors::YELLOW, [TextStyles::BOLD])
);
$outputCompiler = new OutputCompiler($elements);
$output = new ConsoleOutput($outputCompiler);

// Now, pass it into the kernel (assume it's already set up)
global $argv;
exit($kernel->handle($argv, $output));
```

<h4 id="overriding-built-in-elements">Overriding Built-In Elements</h4>

To override a built-in element, just re-register it:

```php
$compiler->registerElement(
    new Element('success', new Style(Colors::GREEN, Colors::BLACK))
);
```