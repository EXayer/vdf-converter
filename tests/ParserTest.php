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
            ['{"a""1""b""2"}', ['a' => 1, 'b' => 2]],
            ['{"a"' . "\n" . '"1"' . "\n" . '"b"' . "\n" . '"2"}', ['a' => 1, 'b' => 2]],
            ['{{"a" "1" "b" "2"}}', ['a' => 1, 'b' => 2]],
            ['{"a" "1" "b" "2"}', ['a' => 1, 'b' => 2]],
            ['"a" {"b" "1"}', ['a' => ['b' => 1]]],
            ['{"a" {"b" {"c" "1"}}}', ['a' => ['b' => ['c' => 1]]]],
            ['{{{"a" {"b" "1"}}}}', ['a' => ['b' => 1]]],
            // escape sequence in key
            ['{"a ' . "\n\t\\\"" . '" {"b" "1"}}', ['a ' . "\n\t\\\"" => ['b' => 1]]],
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
            // nesting
            [
                '"a" {"b" "1" "c" "1" "d" {"e" {"e1" "1" "e2" "2"} "f" {"f1" "1" "f2" "2"}} "g" "1"}',
                [
                    'a' => [
                        'b' => 1,
                        'c' => 1,
                        'd' => [
                            'e' => ['e1' => 1, 'e2' => 2,],
                            'f' => ['f1' => 1, 'f2' => 2,],
                        ],
                        'g' => 1,
                    ],
                ],
            ],
            // duplicate keys, starts with a key
            [
                '"a" {"b" "1" "x" {"x" "1" "x" "2"} "e" {"f" {"x" "1" "" "1" "" "2" "x" "2"} "f" {}} "x" {} "e" {}}',
                [
                    'a' => [
                        'b' => 1,
                        'x' => ['x' => 1, 'x__2' => 2],
                        'e' => [
                            'f' => ['x' => 1, '' => 1, '__2' => 2, 'x__2' => 2],
                            'f__2' => [],
                        ],
                        'x__2' => [],
                        'e__2' => [],
                    ],
                ],
            ],
            // duplicate keys, starts with "{"
            [
                '{"a" "1" "b" {"c" "1" "c" "2"} "a" {"b" {"c" "1" "c" "2"} "b" {}} "a" {} "c" {} "c" {}}',

                [
                    'a' => 1,
                    'b' => ['c' => 1, 'c__2' => 2],
                    'a__2' => [
                        'b' => ['c' => 1, 'c__2' => 2],
                        'b__2' => [],
                    ],
                    'a__3' => [],
                    'c' => [],
                    'c__2' => [],
                ],
            ],
            // all duplicates
            [
                '{"x" "1" "x" {"x" "1" "x" "2"} "x" {"x" {"x" "1" "x" "2"} "x" {}} "x" {} "x" {}}',
                [
                    'x' => 1,
                    'x__2' => ['x' => 1, 'x__2' => 2],
                    'x__3' => [
                        'x' => ['x' => 1, 'x__2' => 2],
                        'x__2' => [],
                    ],
                    'x__4' => [],
                    'x__5' => [],
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
