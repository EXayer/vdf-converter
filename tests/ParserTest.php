<?php

namespace EXayer\VdfConverter\Tests;

use EXayer\VdfConverter\Exception\CouldNotParseException;
use EXayer\VdfConverter\Lexer;
use EXayer\VdfConverter\Parser;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    /**
     * @dataProvider syntaxDataProvider
     * @param $vdf
     * @param $expectedResult
     */
    public function testSyntax($vdf, $expectedResult)
    {
        $parser = new Parser(new Lexer(new \ArrayIterator([$vdf])));
        $result = iterator_to_array($parser);

        $this->assertEquals($expectedResult, $result);
    }

    public function syntaxDataProvider()
    {
        return [
            ['{}', []],
            ['{"" ""}', ['' => '']],
            ['"a" {}', ['a' => []]],
            ['{"a" "1" "b" "2"}', ['a' => 1, 'b' => 2]],
            ['{"a"' . "\n" . '"1"' . "\n" . '"b"' . "\n" . '"2"}', ['a' => 1, 'b' => 2]],
            ['{{"a" "1" "b" "2"}}', ['a' => 1, 'b' => 2]],
            ['{"a" "1" "b" "2"}', ['a' => 1, 'b' => 2]],
            ['"a" {"b" "1"}', ['a' => ['b' => 1]]],
            ['{"a" {"b" {"c" "1"}}}', ['a' => ['b' => ['c' => 1]]]],
            ['{{{"a" {"b" "1"}}}}', ['a' => ['b' => 1]]],
            // starts with a key
            [
                '"a" {"b" {"b1" "1" "b2" "2"} "c" {"d" {"e" {"e1" "1" "e2" "2"} "f" {"f1" "1" "f2" "2"}}}}',
                [
                    'a' => [
                        'b' => ['b1' => 1, 'b2' => 2],
                        'c' => [
                            'd' => [
                                'e' => ['e1' => 1, 'e2' => 2],
                                'f' => ['f1' => 1, 'f2' => 2],
                            ],
                        ],
                    ],
                ],
            ],
            // starts with "{"
            [
                '{"b" {"b1" "1" "b2" "2"} "c" {"d" {"e" {"e1" "1" "e2" "2"} "f" {"f1" "1" "f2" "2"}}}}',
                [
                    'b' => ['b1' => 1, 'b2' => 2],
                    'c' => [
                        'd' => [
                            'e' => ['e1' => 1, 'e2' => 2],
                            'f' => ['f1' => 1, 'f2' => 2],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param string $brokenVdf
     * @dataProvider syntaxErrorDataProvider
     */
    public function testSyntaxError(string $brokenVdf)
    {
        $this->expectException(CouldNotParseException::class);

        iterator_to_array(new Parser(new Lexer(new \ArrayIterator([$brokenVdf]))));
    }

    public function syntaxErrorDataProvider()
    {
        return [
            ['{"a"}'],
            ['{"a" b}'],
            ['{"a"b "c"}'],
            ['{"a""b "c" "d"}'],
            ['{"a" null}'],
            ['{"a" 1}'],
            ['{"a" \'b\'}'],
        ];
    }

    /**
     * @param string $vdf
     * @dataProvider unexpectedEndExceptionDataProvider
     */
    public function testUnexpectedEndError(string $vdf)
    {
        $this->expectExceptionMessage(CouldNotParseException::unexpectedEnding()->getMessage());

        iterator_to_array(new Parser(new Lexer(new \ArrayIterator([$vdf]))));
    }

    public function unexpectedEndExceptionDataProvider()
    {
        return [
            ['{'],
            ['"a"'],
            ['{"a"}'],
            ['"a" {'],
            ['{"a" "1"'],
            ['"a" "1"}'],
            ['{{"a" "1"}'],
            ['"a" {"b" '],
            ['"a" "b" "c"}'],
            ['{"a" {"b" {"c" "1"}}'],
            ['{{{"a" {"b" "1"}}}'],
        ];
    }
}
