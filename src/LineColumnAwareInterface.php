<?php

namespace EXayer\VdfConverter;

interface LineColumnAwareInterface
{
    /**
     * Returns a number of processed lines from the beginning.
     * Starts at one.
     *
     * @return int
     */
    public function getLine(): int;

    /**
     * Returns a number of processed columns in the line.
     * Starts at zero.
     *
     * @return int
     */
    public function getColumn(): int;
}
