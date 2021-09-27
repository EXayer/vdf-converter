<?php

namespace EXayer\VdfConverter\Exception;

class CouldNotParseException extends \Exception
{
    public static function unknownToken(int $line, int $column): self
    {
        return new static("Found an unsuported token at {$line}:{$column}");
    }
}
