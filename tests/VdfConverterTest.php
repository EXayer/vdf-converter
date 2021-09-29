<?php

namespace EXayer\VdfConverter\Tests;

use EXayer\VdfConverter\VdfConverter;
use PHPUnit\Framework\TestCase;

class VdfConverterTest extends TestCase
{
    /**
     * @param string $methodName
     * @param array $expected
     * @param mixed $vdf
     * @dataProvider factoryMethodDataProvider
     */
    public function testFactoryMethods(string $methodName, array $expected, $vdf)
    {
        $iterator = call_user_func(VdfConverter::class . "::$methodName", $vdf);

        $this->assertSame($expected, iterator_to_array($iterator));
    }

    public function factoryMethodDataProvider()
    {
        $expectedResult = ['data' => ['key' => 'value']];

        return [
            ['fromString', $expectedResult, '{"data" {"key" "value"}}'],
            ['fromFile', $expectedResult, __DIR__ . '/samples/file-chunks.txt'],
            ['fromStream', $expectedResult, fopen('data://text/plain,{"data" {"key" "value"}}', 'r')],
            ['fromIterable', $expectedResult, ['{"data" {"key', '" "value"}}']],
            ['fromIterable', $expectedResult, new \ArrayIterator(['{"data" {"key', '" "value"}}'])],
        ];
    }

    public function testPositionProgress()
    {
        $expectedPosition = ['a' => 10, 'b' => 21];
        $iterator = VdfConverter::fromString(' {"a" "1" ' . "\n\t" . ' "b" "2"}  ');

        foreach ($iterator as $key => $value) {
            $this->assertSame($expectedPosition[$key], $iterator->getPosition());
        }
    }
}
