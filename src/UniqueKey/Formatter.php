<?php

namespace EXayer\VdfConverter\UniqueKey;

interface Formatter
{
    /**
     * Returns a formatted key based on original key and duplicate index.
     *
     * @param string $key Original key.
     * @param int    $index Duplicate key index.
     *
     * @return string
     */
    public function buildKeyName(string $key, int $index): string;
}