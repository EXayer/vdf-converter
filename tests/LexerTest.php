<?php

namespace EXayer\VdfConverter\Tests;

use EXayer\VdfConverter\Lexer;
use PHPUnit\Framework\TestCase;

class LexerTest extends TestCase
{
    public function testYieldsTokens()
    {
        $data = ['{}  " ab c " "0" "#item"' . "\n\r" . '"qwerty"   "-1" ""'];
        $expected = ['{', '}', '" ab c "', '"0"', '"#item"', '"qwerty"', '"-1"', '""'];

        $this->assertEquals($expected, iterator_to_array(new Lexer(new \ArrayIterator($data))));
    }

    public function testYieldsTokensWithBOM()
    {
        $data = ["\xEF\xBB\xBF{}"];
        $expected = ['{', '}'];

        $this->assertEquals($expected, iterator_to_array(new Lexer(new \ArrayIterator($data))));
    }

    public function testYieldsTokensSkippingComments()
    {
        $vdf = <<<VDF
{
    "one" "/string one" // A comment
    / A comment until the line ends "two" "string two"
    // A comment until the line ends "three" "string three"
    /* A comment until the line ends */ "four" "string four"
    "five" "/*string five*/" /* A comment */
    "six" "//string six" / A comment
}
VDF;

        $expected = ['{', '"one"', '"/string one"', '"five"', '"/*string five*/"', '"six"', '"//string six"',  '}'];

        $this->assertEquals($expected, iterator_to_array(new Lexer(new \ArrayIterator([$vdf]))));
    }
}
