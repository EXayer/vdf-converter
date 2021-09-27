<?php

namespace EXayer\VdfConverter\Input;

class FileChunks implements \IteratorAggregate
{
    /**
     * @var string
     */
    private $fileName;

    /**
     * @var int
     */
    private $chunkSize;

    /**
     * @param string $fileName
     * @param int $chunkSize
     */
    public function __construct(string $fileName, int $chunkSize = 1024 * 8)
    {
        $this->fileName = $fileName;
        $this->chunkSize = $chunkSize;
    }

    /**
     * @return \Generator
     */
    public function getIterator()
    {
        $fileHandle = fopen($this->fileName, 'r');

        try {
			yield from (new StreamChunks($fileHandle, $this->chunkSize));
        } finally {
            fclose($fileHandle);
        }
    }
}
