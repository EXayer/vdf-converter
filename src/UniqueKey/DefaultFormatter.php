<?php

namespace EXayer\VdfConverter\UniqueKey;

class DefaultFormatter implements Formatter
{
    /**
     * {@inheritDoc}
     */
    public function buildKeyName(string $key, int $index): string
    {
        return $key . '__[' . $index . ']';
    }
}