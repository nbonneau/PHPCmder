<?php
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

namespace Primer\Console\Output\Notify;

use Primer\Console\Output\Writer;

/**
 * The `Spinner` Notifier displays an ASCII spinner.
 */
class Spinner extends Notify
{
    protected $_chars = array(
        '-',
        '\\',
        '|',
        '/',
    );
    protected $_format = '{:msg} {:char}  ({:elapsed}, {:speed}/s)';
    protected $_iteration = 0;

    /**
     * Prints the current spinner position to `STDOUT` with the time elapsed
     * and tick speed.
     *
     * @param boolean $finish `true` if this was called from
     *                          `cli\Notify::finish()`, `false` otherwise.
     *
     * @see cli\out_padded()
     * @see cli\Notify::formatTime()
     * @see cli\Notify::speed()
     */
    public function display($finish = false)
    {
        $msg = $this->_message;
        $idx = $this->_iteration++ % count($this->_chars);
        $char = $this->_chars[$idx];
        $speed = number_format(round($this->speed()));
        $elapsed = $this->formatTime($this->elapsed());

        $this->outPadded_r(
            $this->format(
                $this->_format, compact('msg', 'char', 'elapsed', 'speed')
            ),
            1, "", false,
            Writer::VERBOSITY_NORMAL,
            Writer::CR
        );
    }

    public function setCharSequence(array $characters)
    {
        $this->_chars = $characters;
    }

    public function setFormat($message = true, $moreInfo = true)
    {
        $this->_format = " {:char}";

        if ($message) {
            $this->_format = "{:msg} {$this->_format}";
        }

        if ($moreInfo) {
            $this->_format .= "  ({:elapsed}, {:speed}/s)";
        }
    }
}
