<?php

namespace EXayer\VdfConverter;

class UniqueKey
{
    /**
     * @const
     */
    const DELIMITER = '__';

    /**
     * Holds duplicate key counts for each level of nesting.
     * 'level' => ['key' => 'count']
     *
     * @var array
     */
    private $storage = [];

    /**
     * @param int $level Buffer nesting level.
     * @param string $key
     * @return string
     */
    public function get(int $level, string $key): string
    {
        if (!isset($this->storage[$level][$key])) {
            $this->storage[$level][$key] = 1;

            return $key;
        }

        return $this->buildName($key, ++$this->storage[$level][$key]);
    }

    /**
     * @param int $level
     */
    public function clear(int $level)
    {
        if (isset($this->storage[$level])) {
            $this->storage[$level] = [];
        }
    }

    /**
     * @param string $key
     * @param int $index
     * @return string
     */
    private function buildName(string $key, int $index): string
    {
        return $key . self::DELIMITER . $index;
    }
}
