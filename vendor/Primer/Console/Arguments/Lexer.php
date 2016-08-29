<?php
/**
 * @author Alex Phillips <aphillips@cbcnewmedia.com>
 * Date: 12/22/14
 * Time: 2:29 PM
 */

/**
 * PHP Command Line Tools
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 * @author    James Logsdon <dwarf@girsbrain.org>
 * @copyright 2010 James Logsdom (http://girsbrain.org)
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 */

namespace Primer\Console\Arguments;

use Iterator;
use Primer\Console\Memoize;

class Lexer extends Memoize implements Iterator
{
    private $_items = array();
    private $_index = 0;
    private $_length = 0;
    private $_first = true;

    /**
     * @param array $items A list of strings to process as tokens.
     */
    public function __construct(array $items)
    {
        $this->_items = $items;
        $this->_length = count($items);
    }

    /**
     * The current token.
     *
     * @return string
     */
    public function current()
    {
        return $this->_item;
    }

    /**
     * Peek ahead to the next token without moving the cursor.
     *
     * @return Argument
     */
    public function peek()
    {
        return new Argument($this->_items[0]);
    }

    /**
     * Move the cursor forward 1 element if it is valid.
     */
    public function next()
    {
        if ($this->valid()) {
            $this->_shift();
        }
    }

    /**
     * Return the current position of the cursor.
     *
     * @return int
     */
    public function key()
    {
        return $this->_index;
    }

    /**
     * Move forward 1 element and, if the method hasn't been called before, reset
     * the cursor's position to 0.
     */
    public function rewind()
    {
        $this->_shift();
        if ($this->_first) {
            $this->_index = 0;
            $this->_first = false;
        }
    }

    /**
     * Returns true if the cursor has not reached the end of the list.
     *
     * @return bool
     */
    public function valid()
    {
        return ($this->_index < $this->_length);
    }

    /**
     * Push an element to the front of the stack.
     *
     * @param mixed $item The value to set
     */
    public function unshift($item)
    {
        array_unshift($this->_items, $item);
        $this->_length += 1;
    }

    /**
     * Returns true if the cursor is at the end of the list.
     *
     * @return bool
     */
    public function end()
    {
        return ($this->_index + 1) == $this->_length;
    }

    private function _shift()
    {
        $key = array_shift($this->_items);
        if (preg_match('#\A([^\s\'"=]+)=(.+?)$#', $key, $matches)) {
            $key = $matches[1];
            array_unshift($this->_items, $matches[2]);
        }
        else {
            // Only push index ahead if no value was added
            $this->_index += 1;
        }

        $this->_item = new Argument($key);
        $this->_explode();
        $this->_unmemo('peek');
    }

    private function _explode()
    {
        if (!$this->_item->canExplode) {
            return false;
        }

        foreach ($this->_item->exploded as $piece) {
            $this->unshift('-' . $piece);
        }
    }
}
