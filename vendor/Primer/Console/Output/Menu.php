<?php
/**
 * @author Alex Phillips <aphillips@cbcnewmedia.com>
 * Date: 12/22/14
 * Time: 11:32 AM
 */

namespace Primer\Console\Output;

use Primer\Console\ConsoleObject;

class Menu extends ConsoleObject
{
    private $_items = array();
    private $_title = '';

    public function __construct($items, $default = null, $title = 'Choose an item') {
        parent::__construct();

        $this->_items = $items;

        $this->_title = $title;
        if ($default && strpos($title, '[') === false && isset($items[$default])) {
            $this->_title .= " [{$items[$default]}]";
        }
    }

    public function prompt()
    {
        $map = array_values($this->_items);
        foreach ($map as $idx => $item) {
            $this->out($this->format('  %d. %s', $idx + 1, (string)$item));
        }
        $this->line();

        while (true) {
            $this->out("{$this->_title}: ", 0);
            $line = $this->input();

            if (is_numeric($line)) {
                $line--;
                if (isset($map[$line])) {
                    return array_search($map[$line], $this->_items);
                }

                if ($line < 0 || $line >= count($map)) {
                    $this->err('Invalid menu selection: out of range');
                }
            }
            else {
                if (isset($default)) {
                    return $default;
                }
            }
        }
    }

    /**
     * Takes input from `STDIN` in the given format. If an end of transmission
     * character is sent (^D), an exception is thrown.
     *
     * @param string  $format  A valid input format. See `fscanf` for documentation.
     *                         If none is given, all input up to the first newline
     *                         is accepted.
     * @param boolean $hide    If true will hide what the user types in.
     *
     * @return string  The input with whitespace trimmed.
     * @throws \Exception  Thrown if ctrl-D (EOT) is sent as input.
     */
    public function input($format = null, $hide = false)
    {
        if ($hide) {
            $this->hide();
        }

        if ($format) {
            fscanf(STDIN, $format . "\n", $line);
        }
        else {
            $line = fgets(STDIN);
        }

        if ($hide) {
            $this->hide(false);
            $this->line();
        }

        if ($line === false) {
            throw new \Exception('Caught ^D during input');
        }

        return trim($line);
    }
}