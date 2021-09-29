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
        $token = null;

        $key = null;
        $value = null;
        $isKeyExpecting = true;
        $isLevelKeyExists = false;
        $level = -1;
        $levelYield = -2;               // either 0 (vdf starts from key) or -1 (vdf starts from "{")

        $buffer = [];
        $bufferLevel = 0;
        $refs = [&$buffer];

        foreach ($lexer as $token) {
            $isYieldAllowed = true;

            switch ($token[0]) {
                case '"':
                    if (substr($token, -1) !== '"' || strlen($token) === 1) {
                        throw CouldNotParseException::wrongQuotedToken($this->getLine(), $this->getColumn());
                    }

                    if ($isKeyExpecting) {
                        $key = $this->unquote($token);
                        $isKeyExpecting = false;
                        $isYieldAllowed = false;

                        if ($levelYield === -2) {
                            $levelYield = -1;
                        }
                    } else {
                        $value = $this->unquote($token);
                        $isKeyExpecting = true;
                    }

                    break;

                case '{':
                    ++$level;

                    if ($levelYield === -2) {
                        $levelYield = 0;
                    }

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
                $isLevelKeyExists = true;
            }

            if ($level === $levelYield) {
                $bufferCount = count($buffer);

                if ($bufferCount > 0) {
                    if ($bufferCount == 1) {
                        reset($buffer);
                        $bufferKey = key($buffer);
                        yield $bufferKey => $buffer[$bufferKey];
                    } else {
                        yield from new \ArrayIterator($buffer);
                    }

                    $buffer = [];
                    $refs = [&$buffer];
                    $bufferLevel = 0;
                }
            }
        }

        if ($token === null) {
            throw CouldNotParseException::emptyVdf();
        }

        if ($level != -1 || !empty($buffer) || $key !== null || $value !== null) {
            throw CouldNotParseException::unexpectedEnding();
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
