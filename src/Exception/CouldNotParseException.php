<?php

namespace EXayer\VdfConverter\Exception;

class CouldNotParseException extends \Exception
{
    public static function unknownToken(int $line, int $column): self
    {
        return new static("Found an unsupported token at {$line}:{$column}");
    }

    public static function wrongQuotedToken(int $line, int $column): self
    {
        return new static("Found a wrong quoted token at {$line}:{$column}");
    }

    public static function emptyVdf(): self
    {
        return new static("VDF string is empty");
    }

    public static function unexpectedEnding(): self
    {
        return new static("VDF string ended unexpectedly");
    }
}
