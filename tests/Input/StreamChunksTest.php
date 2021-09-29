<?php

namespace EXayer\VdfConverter\Tests\Input;

use EXayer\VdfConverter\Input\StreamChunks;
use PHPUnit\Framework\TestCase;

class StreamChunksTest extends TestCase
{
    public function testThrowsOnInvalidResource()
    {
        $this->expectException(\InvalidArgumentException::class);

        new StreamChunks(false);
    }

    public function testYieldsData()
    {
        $result = iterator_to_array(new StreamChunks(fopen('data://text/plain,test', 'r')));

        $this->assertSame(['test'], $result);
    }
}
