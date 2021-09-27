<?php

namespace EXayer\VdfConverter;

interface PositionAwareInterface
{
    /**
     * Returns a number of processed bytes from the beginning.
     *
     * @return int
     */
    public function getPosition(): int;
}
