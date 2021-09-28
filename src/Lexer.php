<?php

namespace EXayer\VdfConverter;

use IteratorAggregate;

class Lexer implements IteratorAggregate, PositionAwareInterface, LineColumnAwareInterface
{
    /**
     * @var iterable
     */
    private $bytesIterator;

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var int
     */
    private $line = 1;

    /**
     * @var int
     */
    private $column = 0;

    /**
     * @param iterable $byteChunks
     */
    public function __construct($byteChunks)
    {
        $this->bytesIterator = $byteChunks;
    }

    /**
     * @return \Generator
     */
    public function getIterator()
    {
        $buffer = '';
        $bufferLength = 0;
        $position = 0;
        $column = 0;

        $inString = false;
        $isEscaping = false;
        $trackingLineBreak = false;
        $isComment = false;

        // Read UTF-8 BOM bytes as whitespace
        ${"\xEF"} = ${"\xBB"} = ${"\xBF"} = 0;

        ${' '} = 0;
        ${"\n"} = 0;
        ${"\r"} = 0;
        ${"\t"} = 0;
        ${'/'} = 1;
        ${'{'} = 1;
        ${'}'} = 1;

        foreach ($this->bytesIterator as $bytes) {
            $bytesLength = strlen($bytes);
            for ($i = 0; $i < $bytesLength; ++$i) {
                $byte = $bytes[$i];
                ++$position;

                if ($inString) {
                    $isQuotes = $byte === '"';
                    $inString = !($isQuotes && !$isEscaping);

                    $isBackslash = $byte === '\\';
                    $isEscaping = ($isBackslash && !$isEscaping);

                    $buffer .= $byte;
                    ++$bufferLength;

                    continue;
                }

                if ($isComment) {
                    if (($trackingLineBreak = $byte === "\r") || $byte === "\n") {
                        $this->line++;
                        $column = 0;
                        $isComment = false;
                    }

                    continue;
                }

                // handle CRLF newlines
                if ($trackingLineBreak && $byte === "\n") {
                    $trackingLineBreak = false;

                    continue;
                }

                if (isset($$byte)) {
                    ++$column;

                    if ($buffer !== '') {
                        $this->position = $position;
                        $this->column = $column;

                        yield $buffer;

                        $column += $bufferLength;
                        $buffer = '';
                        $bufferLength = 0;
                    }

                    // is not whitespace
                    if ($$byte) {
                        if ($byte === '/') {
                            $isComment = true;
                            // found a bracket: '{', '}'
                        } else {
                            $this->position = $position;
                            $this->column = $column;

                            yield $byte;
                        }
                    } elseif (($trackingLineBreak = $byte === "\r") || $byte === "\n") {
                        $this->line++;
                        $column = 0;
                        $isComment = false;
                    }
                } else {
                    if ($byte === '"') {
                        $inString = true;
                    }

                    $buffer .= $byte;
                    ++$bufferLength;
                }
            }
        }

        if ($buffer !== '') {
            $this->position = $position;
            $this->column = $column;

            yield $buffer;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * {@inheritDoc}
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * {@inheritDoc}
     */
    public function getColumn(): int
    {
        return $this->column;
    }
}
