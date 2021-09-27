<?php

namespace EXayer\VdfConverter;

use EXayer\VdfConverter\Exception\CouldNotParseException;
use Traversable;

class Parser implements \IteratorAggregate, PositionAwareInterface, LineColumnAwareInterface
{
    /**
     * @var Traversable
     */
    private $lexer;

    /**
     * @param Traversable $lexer
     */
    public function __construct(Traversable $lexer)
    {
        $this->lexer = $lexer;
    }

    /**
     * @return \Generator
     * @throws CouldNotParseException
     */
    public function getIterator()
    {
        $lexer = $this->lexer;
        $key = null;
        $value = null;
        $isKeyExpecting = true;
        $isLevelKeyExists = false;
        $level = -1;

        $buffer = [];
        $bufferLevel = 0;
        $refs = [&$buffer];

        foreach ($lexer as $token) {
            $isYieldAllowed = true;

            switch ($token[0]) {
                case '"':
                    if ($isKeyExpecting) {
                        $key = $this->unquote($token);
                        $isKeyExpecting = false;
                        $isYieldAllowed = false;
                    } else {
                        $value = $this->unquote($token);
                        $isKeyExpecting = true;
                    }

                    break;

                case '{':
                    ++$level;

                    if ($key !== null) {
                        if ($isLevelKeyExists) {
                            $refs[$bufferLevel][$key] = [];
                            $isLevelKeyExists = false;
                        } else {
                            $refs[$bufferLevel] = [$key => []];
                        }

                        $refs[] = &$refs[$bufferLevel][$key];
                        ++$bufferLevel;

                        $key = null;
                        $isKeyExpecting = true;
                        $isYieldAllowed = false;
                    }

                    break;

                case '}':
                    --$level;

                    if (!empty($buffer)) {
                        array_pop($refs);
                        --$bufferLevel;

                        $isLevelKeyExists = true;
                    }

                    break;

                default:
                    throw CouldNotParseException::unknownToken($this->getLine(), $this->getColumn());
            }

            if (!$isYieldAllowed) {
                continue;
            }

            if ($key !== null && $value !== null) {
                $refs[$bufferLevel][$key] = $value;

                $key = null;
                $value = null;
            }

            if ($level === 0) {
                if ($value !== null) {
                    yield $key => $value;

                    $key = null;
                    $value = null;
                    $isKeyExpecting = true;
                } else if (!empty($buffer)) {
                    reset($buffer);
                    $bufferKey = key($buffer);
                    yield $bufferKey => $buffer[$bufferKey];

                    $buffer = [];
                    $refs = [&$buffer];
                    $bufferLevel = 0;
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getPosition(): int
    {
        if (!$this->lexer instanceof PositionAwareInterface) {
            return -1;
        }

        return $this->lexer->getPosition();
    }

    /**
     * {@inheritDoc}
     */
    public function getLine(): int
    {
        if (!$this->lexer instanceof LineColumnAwareInterface) {
            return 0;
        }

        return $this->lexer->getLine();
    }

    /**
     * {@inheritDoc}
     */
    public function getColumn(): int
    {
        if (!$this->lexer instanceof LineColumnAwareInterface) {
            return -1;
        }

        return $this->lexer->getColumn();
    }

    /**
     * @param $string
     *
     * @return false|string
     */
    private function unquote($string)
    {
        return substr($string, 1, -1);
    }
}
