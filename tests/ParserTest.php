<?php

namespace EXayer\VdfConverter\Tests;

use EXayer\VdfConverter\Lexer;
use EXayer\VdfConverter\Parser;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    /**
     * @param $vdf
     * @param $expectedResult
     * @dataProvider syntaxDataProvider
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
            ['"a" {}', ['a' => []]],
            ['{"a" "b"}', ['a' => 'b']],
            ['{"a" {"b" {"c" "1"}}}', ['a' => ['b' => ['c' => 1]]]],
            ['{{"a" "1"}}', ['a' => 1]],
            ['{"a" "1" "b" "2"}', ['a' => 1, 'b' => 2]],
            ['"a" {"b" "1"}', ['a' => ['b' => 1]]],
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
                '{"b" {"b1" "1" "b2" "2"} "c" {"d" {"e" {"e1" "1" "e2" "2"} "f" {"f1" "1" "f2" "2"}}}',
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
}
