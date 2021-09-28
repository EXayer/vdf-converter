<?php

namespace EXayer\VdfConverter\Tests;

use EXayer\VdfConverter\Input\StringChunks;
use EXayer\VdfConverter\Lexer;
use PHPUnit\Framework\TestCase;

class LexerTest extends TestCase
{
    public function testYieldsTokens()
    {
        $data = ['{}  " ab c " "0" "#item"' . "\n\r\t\t" . '"qwerty"   "-1" "" "\"three\""'];
        $expected = ['{', '}', '" ab c "', '"0"', '"#item"', '"qwerty"', '"-1"', '""', '"\"three\""'];

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

    /**
     * @param string $vdfFilePath
     * @dataProvider newLinesFilePathDataProvider
     */
    public function testProvidesLocationalData(string $vdfFilePath)
    {
        $vdfStr = file_get_contents($vdfFilePath);
        $lexer = new Lexer(new StringChunks($vdfStr));
        $positions = $this->lineColumnAwareDataProvider();

        $i = 0;
        foreach ($lexer as $token) {
            $i++;
            $position = array_shift($positions);

            $this->assertEquals($position[0], $token, 'token failed #' . $i);
            $this->assertEquals($position[1], $lexer->getLine(), 'line failed #' . $i);
            $this->assertEquals($position[2], $lexer->getColumn(), 'column failed #' . $i);
        }
    }

    public function newLinesFilePathDataProvider()
    {
        return [
            'CR newlines' => [__DIR__ . '/samples/CR-newlines.txt'],
            'LF newlines' => [__DIR__ . '/samples/LF-newlines.txt'],
            'CRLF newlines' => [__DIR__ . '/samples/CRLF-newlines.txt'],
        ];
    }

    private function lineColumnAwareDataProvider()
    {
        // token, line, column
        return [
            ['"treasure_chest"', 1, 1],
            ['{', 2, 1],
            ['"Blades"', 3, 5],
            ['"1"', 3, 15],
            ['"Aeons"', 4, 5],
            ['"1"', 4, 14],
            ['"Resistive"', 5, 5],
            ['"1"', 5, 18],
            ['"Signet"', 6, 5],
            ['"1"', 6, 15],
            ['"Staff"', 7, 5],
            ['"1"', 7, 14],
            ['"additional_drop"', 8, 5],
            ['{', 9, 5],
            ['"chance"', 10, 9],
            ['"1"', 10, 19],
            ['"item"', 11, 9],
            ['"Desolation"', 11, 17],
            ['}', 12, 5],
            ['"additional_drop"', 13, 5],
            ['{', 14, 5],
            ['"chance"', 15, 9],
            ['"1"', 15, 19],
            ['"item"', 16, 9],
            ['"Golden Blades"', 16, 17],
            ['}', 17, 5],
            ['}', 18, 1],
        ];
    }
}
