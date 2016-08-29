<?php
/**
 * StyleFormatter
 *
 * @author Alex Phillips <exonintrendo@gmail.com>
 */

namespace Primer\Console\Output;

use Primer\Console\XmlParser;

class StyleFormatter
{
    public static $fgColors = array(
        'black'        => '0;30',
        'dark_gray'    => '1;30',
        'blue'         => '0;34',
        'light_blue'   => '1;34',
        'green'        => '0;32',
        'light_green'  => '1;32',
        'cyan'         => '0;36',
        'light_cyan'   => '1;36',
        'red'          => '0;31',
        'light_red'    => '1;31',
        'purple'       => '0;35',
        'light_purple' => '1;35',
        'brown'        => '0;33',
        'yellow'       => '1;33',
        'light_gray'   => '0;37',
        'white'        => '1;37',
    );

    public static $bgColors = array(
        'black'   => 40,
        'red'     => 41,
        'green'   => 42,
        'brown'   => 43,
        'blue'    => 44,
        'magenta' => 45,
        'cyan'    => 46,
        'white'   => 47,
    );

    public static $effects = array(
        'defaults'  => 0,
        'bold'      => 1,
        'underline' => 4,
        'blink'     => 5,
        'reverse'   => 7,
        'conceal'   => 8,
    );

    private $_fgColor;
    private $_bgColor;
    private $_effects;

    public function __construct($fgColor, $bgColor = '', array $effects = array())
    {
        $this->_fgColor = $fgColor;
        $this->_bgColor = $bgColor;
        $this->_effects = $effects;
    }

    public function render($xmlTag, $message)
    {
        $values = XmlParser::getValueBetweenTags($xmlTag, $message);
        $buildNewMessage = $message;
        foreach ($values as $val) {
            $valueReplaced = '<' . $xmlTag . '>' . $val . '</' . $xmlTag . '>';
            $valueResult = $this->_replaceTagColors($val);

            $buildNewMessage = str_replace($valueReplaced, $valueResult, $buildNewMessage);
        }

        return $buildNewMessage;
    }

    private function _replaceTagColors($text)
    {
        $colors = $this->getBgColorCode() . ';' . $this->getFgColorCode();
        $effects = $this->getParsedToStringEffects();
        $effectsCodeString = $effects ? ';' . $effects : '';

        return sprintf("\033[%sm%s\033[0m", $colors . $effectsCodeString, $text);
    }

    public function getBgColorCode()
    {
        return isset(self::$bgColors[$this->_bgColor]) ? self::$bgColors[$this->_bgColor] : '';
    }

    public function getFgColorCode()
    {
        return isset(self::$fgColors[$this->_fgColor]) ? self::$fgColors[$this->_fgColor] : '';
    }

    public function getParsedToStringEffects()
    {
        $effectCodeList = array();
        if (!empty($this->_effects)) {
            foreach ($this->_effects as $effectName) {
                $effectCodeList[] = $this->getEffectCode($effectName);
            }
        }
        $effectString = implode(';', $effectCodeList);
        return $effectString;
    }

    public function getEffectCode($effect)
    {
        return self::$effects[$effect];
    }
}