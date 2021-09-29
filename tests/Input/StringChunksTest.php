<?php

namespace EXayer\VdfConverter\Tests\Input;

use EXayer\VdfConverter\Input\StringChunks;
use PHPUnit\Framework\TestCase;

class StringChunksTest extends TestCase
{
    /**
     * @param string $string
     * @param int $chunkSize
     * @param array $expectedResult
     * @dataProvider stringChunksDataProvider
     */
    public function testYieldsStringChunks(string $string, int $chunkSize, array $expectedResult)
    {
        $stringBytes = new StringChunks($string, $chunkSize);
        $result = iterator_to_array($stringBytes);

        $this->assertSame($expectedResult, $result);
    }

    public function stringChunksDataProvider()
    {
        return [
            ['qwerty', 6, ['qwerty']],
            ['qwerty', 7, ['qwerty']],
            ['qwerty', 3, ['qwe', 'rty']],
            ['qwerty', 4, ['qwer', 'ty']],
        ];
    }
}
