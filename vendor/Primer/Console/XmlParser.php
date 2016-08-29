<?php
/**
 * XmlParser
 *
 * @author Alex Phillips <exonintrendo@gmail.com>
 */

namespace Primer\Console;

class XmlParser
{
    public static function parseTags($stringToParse)
    {
        preg_match_all('#<([\w-]*?)>#', $stringToParse, $tagsMatched);
        return $tagsMatched[1];
    }

    public static function getValueBetweenTags($tag, $stringToParse)
    {
        $regexp = '#<' . $tag . '>(.*?)</' . $tag . '>#s';
        preg_match_all($regexp, $stringToParse, $valuesMatched);
        return $valuesMatched[1];
    }
}