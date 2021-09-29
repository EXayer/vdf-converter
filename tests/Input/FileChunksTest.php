<?php

namespace EXayer\VdfConverter\Tests\Input;

use EXayer\VdfConverter\Input\FileChunks;
use PHPUnit\Framework\TestCase;

class FileChunksTest extends TestCase
{
    /**
     * @param int $chunkSize
     * @param array $expectedResult
     * @dataProvider data_testGeneratorYieldsFileChunks
     */
    public function testYieldsFileChunks(int $chunkSize, array $expectedResult)
    {
        $fileChunks = new FileChunks(__DIR__ . '/../samples/file-chunks.txt', $chunkSize);
        $result = iterator_to_array($fileChunks);

        $this->assertSame($expectedResult, $result);
    }

    public function data_testGeneratorYieldsFileChunks()
    {
        return [
            [5, ['{"dat', 'a" {"', 'key" ', '"valu', 'e"}}'. "\n"]],
            [6, ['{"data', '" {"ke', 'y" "va', 'lue"}}', "\n"]],
            [1024 * 8, ['{"data" {"key" "value"}}' . "\n"]],
        ];
    }
}
