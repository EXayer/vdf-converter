<?php

namespace EXayer\VdfConverter;

use EXayer\VdfConverter\Input\FileChunks;
use EXayer\VdfConverter\Input\StreamChunks;
use EXayer\VdfConverter\Input\StringChunks;

class VdfConverter implements \IteratorAggregate, PositionAwareInterface
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
     * @param iterable                $bytesIterator
     * @param VdfConverterConfig|null $config
     */
    public function __construct($bytesIterator, VdfConverterConfig $config = null)
    {
        $this->bytesIterator = $bytesIterator;

        $config = $config ?: new VdfConverterConfig();
        $this->parser = new Parser(new Lexer($this->bytesIterator), $config);
    }

    /**
     * @param string                  $string
     * @param VdfConverterConfig|null $config
     *
     * @return self
     */
    public static function fromString(string $string, VdfConverterConfig $config = null): self
    {
        return new static(new StringChunks($string), $config);
    }

    /**
     * @param string                  $fileName
     * @param VdfConverterConfig|null $config
     *
     * @return self
     */
    public static function fromFile(string $fileName, VdfConverterConfig $config = null): self
    {
        return new static(new FileChunks($fileName), $config);
    }

    /**
     * @param resource                $stream
     * @param VdfConverterConfig|null $config
     *
     * @return self
     */
    public static function fromStream($stream, VdfConverterConfig $config = null): self
    {
        return new static(new StreamChunks($stream), $config);
    }

    /**
     * @param \Traversable|array      $iterable
     * @param VdfConverterConfig|null $config
     *
     * @return self
     */
    public static function fromIterable($iterable, VdfConverterConfig $config = null): self
    {
        return new static($iterable, $config);
    }

    /**
     * @return \Generator
     * @throws Exception\CouldNotParseException
     */
    public function getIterator()
    {
        return $this->parser->getIterator();
    }

    /**
     * {@inheritDoc}
     */
    public function getPosition(): int
    {
        return $this->parser->getPosition();
    }
}
