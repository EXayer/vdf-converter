<?php

namespace EXayer\VdfConverter\Input;

class StreamChunks implements \IteratorAggregate
{
    /**
     * @var resource
     */
    private $stream;

    /**
     * @var int
     */
    private $chunkSize;

    /**
     * @param resource $stream
     * @param int $chunkSize
     */
    public function __construct($stream, int $chunkSize = 1024 * 8)
    {
        if (!is_resource($stream) || get_resource_type($stream) !== 'stream') {
            throw new \InvalidArgumentException('$stream must be a valid stream resource.');
        }

        $this->stream = $stream;
        $this->chunkSize = $chunkSize;
    }

    /**
     * @return \Generator
     */
    public function getIterator()
    {
        while (($chunk = fread($this->stream, $this->chunkSize)) !== '') {
            yield $chunk;
        }
    }
}
