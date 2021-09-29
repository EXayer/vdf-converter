<?php

namespace EXayer\VdfConverter;

use EXayer\VdfConverter\Input\FileChunks;
use EXayer\VdfConverter\Input\StreamChunks;
use EXayer\VdfConverter\Input\StringChunks;

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
     * @param string $string
     *
     * @return self
     */
    public static function fromString(string $string): self
    {
        return new static(new StringChunks($string));
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
     * @param resource $stream
     *
     * @return self
     */
    public static function fromStream($stream): self
    {
        return new static(new StreamChunks($stream));
    }

    /**
     * @return \Generator
     * @throws Exception\CouldNotParseException
     */
    public function getIterator()
    {
        return $this->parser->getIterator();
    }
}
