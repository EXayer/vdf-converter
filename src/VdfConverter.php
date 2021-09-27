<?php

namespace EXayer\VdfConverter;

use EXayer\VdfConverter\Input\FileChunks;

class VdfConverter implements \IteratorAggregate
{
    /**
     * @var iterable
     */
    private $bytesIterator;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @param iterable $bytesIterator
     */
    public function __construct($bytesIterator)
    {
        $this->bytesIterator = $bytesIterator;
        $this->parser = new Parser(new Lexer($this->bytesIterator));
    }

    /**
     * @param string $file
     *
     * @return self
     */
    public static function fromFile(string $file): self
    {
        return new static(new FileChunks($file));
    }

    /**
     * @return \Generator
     */
    public function getIterator()
    {
        return $this->parser->getIterator();
    }
}
