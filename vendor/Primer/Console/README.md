Primer Console Framework
========================

This framework can be used to creating fully shell scripts. Originally code from   https://github.com/piotrooo/php-shell-framework and https://github.com/wp-cli/php-cli-tools, it
has been modified to be integraded and run as a 'framework' with the best parts of both repos.

There is a demo class (DemoCommand) that you can add to your application and run for an example of all
the implementations of the features listed here.

This has been tied into the Primer PHP Framework but can be used independently without any dependencies.

# Creating new application

To create new application, create a new Console application and calling run.

```php
<?php
/*
 * Console.php
 */
$app = new \Primer\Console\Console();
$app->run();
```

Then simply run the php script from the command line.

# Creating Commands

Newly created file should have number of requirements:
* Class should extends from `BaseCommand`.
* Must include the 'configure' method which is where you specify the name, an optional description,
and any necessary flags, options, and arguments.
* Must include the `run` method which is automatically called to initiate and run the command.

An example command may look like this:

```php
<?php
class HelloCommand extends \Primer\Console\Command\BaseCommand
{
  public function configure()
  {
    $this->setName('hello');
    $this->addArgument('name');
    $this->addFlag('yell', 'y', Primer\Console\Input\DefinedInput::VALUE_OPTIONAL, 'Output text in all caps');
  }

  public function run()
  {
    if ($name = $this->args->getArgument('name')) {
      $message = "Hello, $name";
    }
    else {
      $message = "Hello, World";
    }

    if ($this->args->getFlag('y')) {
      $message = strtoupper($message);
    }

    $this->out($message);
  }
}
```

And executing it would look like this:

```bash
$ php example.php hello Alex
Hello, Alex

$ php example.php hello Alex -y
HELLO, ALEX
```

## Adding Flags, Options, and Arguments

You can add flags, options, and arguments to your command. Flags are boolean arguments that do not accept a
value (i.e. '-h' or '--help'). Options are arguments that, if a value isn't specified, is given the value of
'true' if included. Arguments are values that do not need to be marked but rather a value is just included
in the command (see 'Hello World' example above);

### Arguments

Arguments are added with the 'addArgument()' method and only require the argument name and optionally a description.

```php
<?php
$this->setArgument('name', Primer\Console\Input\DefinedInput::VALUE_OPTIONAL, 'The persons name to output');
```

### Flags

Flags can be added using the 'addFlag()' method inside of the setup method of your command. A name is required, but you can optionally pass in a 'shortcut' (alias), a mode (whether the command requires the flag or not), and a description which is used when generating the help screen for your command.

```php
<?php
$this->addFlag('yell', 'y', Primer\Console\Input\DefinedInput::VALUE_OPTIONAL, 'Output the greeting in all caps');
```

### Options

Options are added the same way as flags (addOption() method) except it accepts an additional parameter of 'default value' to set the option to if the option is included but no value is specified on the command line. Otherwise, the option is given a boolean value.

As an example, we can use the option 'name' (instead of the argument) in the above program which will accept a value designated by the 'option' parameter.

```php
<?php
$this->addOption('name', 'n', Primer\Console\Input\DefinedInput::VALUE_OPTIONAL, 'The name of the person we are greeting');
```

It would then be included at execution like this:

```bash
$ php example.php hello --name Alex
Hello, Alex
```

### Accessing Arguments

Flags, options, and arguments are all accessible through the command's inherited variable 'args' through the methods
getFlag, getOption, and getArgument. These each accept 1 parameter, the name (or alias) of the desired argument.

If a flag or option has an alias in addition to its primary name, calling the getter method with either the name or
the alias will retrieve the same variable.

If the flag, option, or argument exists, the associated value is returned. Otherwise, the default value is returned (false for flags,
  null for options unless otherwise set, and null for arguments).

# Running application

Note: running the application without any arguments will output the application's default help screen. Alternatively,
passing the help option (-h|--help) in addition to a command will output the auto-generated help screen for
that command.

After creating the command, you'll need to add it to the application. Pass either the class name
or an instance of the class. After adding the command, we want run it from our console.

```php
<?php
$app->addCommand(new HelloCommand());
```

### Simply call from shell
```bash
$ php console.php hello -y Alex
HELLO, ALEX
```

### Calling with arguments

```bash
$ php console.php hello Alex
Hello, Alex
```

# Output

When you want display someting on `STDOUT` you can use `out` method:

```php
$this->out("This is a message for you!");
```

print:

```
Hello World Today!!!
```

The out method also accepts an integer value of how many new lines you want after the output, the verbosity level,
and the end-of-line character you would like used. These are all optional.

### Console output levels

Sometimes you need different levels of verbosity. PHP Shell Framework provide three levels:

1. QUIET
2. NORMAL
3. VERBOSE

Default all outputs working in `NORMAL` level. If you want change level you must define this in `out` method.

__Example:__

```php
$this->out('This message is in normal verbosity');
$this->out('This message is in quiet verbosity', 1, \Primer\Console\Output\Writer::VERBOSITY_QUIET);
$this->out('This message is in verbose verbosity', 1, \Primer\Console\Output\Writer::VERBOSITY_VERBOSE);
```

If you want run application in `NORMAL` level:

    $ php console.php hello

output:

    This message is in normal verbosity
    This message is in quiet verbosity

If you want run application in `QUIET` level:

    $ php console.php hello --quiet

output:

    This message is in quiet verbosity

If you want run application in `VERBOSE` level:

    $ php console.php hello --verbose

output:

    This message is in normal verbosity
    This message is in quiet verbosity
    This message is in verbose verbosity

# Styling output

Styling output is done by user-defined tags - like XML. The framework's style formetter will replace XML tag to correct defined ANSI code sequence.
By default, the following are predefined:
* ```<info>``` - green text
* ```<warning>``` - yellow text
* ```<error>``` - red rext

To declare new XML tag and corresonding with him ANSI code you do:

```php
$styleFormat = new \Primer\Console\Output\StyleFormatter('gray', 'magenta', array('blink', 'underline'));
$this->setFormatter('special', $styleFormat);
```

This would you to allow `<special>` tag in you output messages and will set text color to `gray`, background color to `magenta` and have two effects - `blink` and `underline`.

```php
$this->out("<special>Hello</special> World!!!");
```

You can use following color for text attributes:

* black
* red
* green
* brown
* blue
* magenta
* cyan
* gray

For background color use:

* black
* red
* green
* brown
* blue
* magenta
* cyan
* white

Also you can use following effects:

* defaults
* bold
* underline
* blink
* reverse
* conceal

# Reading

Method `ask` reads and returns characters from `STDIN`, which usually receives what the user inputs. You can pass
in an optional string to pose as a prompt or question.

```php
$this->ask("How old are you: ");
$this->out('You are ' . $age . ' years old - nice!');
```

# Additional Features

The framework also includes several features to aid in outputting information to the user.

## Dots

The 'Dots' object displays a 'waiting' type notation while tasks are being completed. You can add these by
adding the following to your code:

```php
$dots = new Primer\Console\Output\Notify\Dots;("Loading");
for ($i = 0; $i < 5; $i++) {
    $dots->tick();

    // Put necessary code to perform here

    usleep(100000);
}
$dots->finish();
```

## Spinner

Spinner is similar to the Dots feature, however, it will display an ASCII spinner instead. You can also
override the character sequence displayed by calling 'setCharSequence' with an array of characters

```php
$spinner = new Spinner("Please wait");
for ($i = 0; $i < 100; $i++) {
    $spinner->tick();

    // Action code here

    usleep(100000);
}
$spinner->finish();
```

### Table

The 'Table' class will generate an ASCII table for displaying data in the terminal.

```php
$table = new Primer\Console\Output\Table\Table();
$table->setHeaders(array('ID', 'Name', 'Surname'))
$table->setRows(array(
        array('1', 'John', 'Smith'),
        array('2', 'Brad', 'Pitt'),
        array('3', 'Denzel', 'Washington'),
        array('4', 'Angelina', 'Jolie')
    ));
$table->render($this->getStdout());
```

will generate:

    +----+----------+------------+
    | ID | Name     | Surname    |
    +----+----------+------------+
    | 1  | John     | Smith      |
    | 2  | Brad     | Pitt       |
    | 3  | Denzel   | Washington |
    | 4  | Angelina | Jolie      |
    +----+----------+------------+

Additionaly we can add single row to our table by using `addRow` method:

```php
$table->addRow(array('5', 'Peter', 'Nosurname'));
```

will produce:

    +----+----------+------------+
    | ID | Name     | Surname    |
    +----+----------+------------+
    | 1  | John     | Smith      |
    | 2  | Brad     | Pitt       |
    | 3  | Denzel   | Washington |
    | 4  | Angelina | Jolie      |
    | 5  | Peter    | Nosurname  |
    +----+----------+------------+

## Progress bar

A progress bar is generated and used the same was as the Dots and Spinner classes.

```php
$test = new Primer\Console\Output\Notify\Bar("Progress", 10);
for ($i = 0; $i < 10; $i++) {
    $test->tick();
    usleep(100000);
}
$test->finish();
```

will produce:

    Test  80% [==============================>    ] 0:01 / 0:01

A progress bar's length is automatically generated to fill the width of the window

## Tree

The Tree class allows you to generate a tree structure given a multi-dimensional array in 2 different outputs:
ASCII and Markdown. By default, a tree will be rendered in Markdown.

ASCII example:

```php
$data = [
  'Test' => [
    'Something Cool' => [
      'This is a 3rd layer',
    ],
    'This is a 2nd layer',
  ],
  'Other test' => [
    'This is awesome' => [
      'This is also cool',
      'This is even cooler',
      'Wow like what is this' => [
        'Awesome eh?',
        'Totally' => [
          'Yep!'
        ],
      ],
    ],
  ],
];

// ASCII Tree
$this->out("ASCII:");
$tree = new Tree();
$tree->setData($data);
$tree->setRenderer(new Ascii());
$tree->display();

// Markdown Tree
$this->out("Markdown:");
$tree = new Tree();
$tree->setData($data);
$tree->display();
```

The above code will output the following:

```bash
ASCII:
|-Array
| |-Array
| | \-This is a 3rd layer
| \-This is a 2nd layer
\-Array
  \-Array
  |-This is also cool
  |-This is even cooler
  \-Array
    |-Awesome eh?
    \-Array
      \-Yep!

Markdown:
- Test
    - Something Cool
        - This is a 3rd layer
    - This is a 2nd layer
- Other test
    - This is awesome
        - This is also cool
        - This is even cooler
        - Wow like what is this
            - Awesome eh?
            - Totally
                - Yep!
```

## Menu

Although you can use the 'ask' method to prompt the user for input, there is an added feature to generate
a menu from a data structure and prompt the user to select a choice. The returned value is the key of the
choice in the array.

```php
$menu = array(
  'yes' => 'Absolutely',
  'no' => 'Nope!',
);

$menu = new Menu($menu, null, 'Are you having fun?');
```

Will generate:

```bash
1. Absolutely!
2. Nope!

Are you having fun?
```
