<?php

namespace EXayer\VdfConverter\UniqueKey;

class UniqueKeyHandler
{
    /**
     * @var Formatter
     */
    private $formatter;

    /**
     * Holds duplicate key counts for each level of nesting.
     * 'level' => ['key' => 'count']
     *
     * @var array
     */
    private $storage = [];

    /**
     * @param Formatter $formatter
     */
    public function __construct(Formatter $formatter)
    {
        $this->formatter = $formatter;
    }

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

        return $this->formatter->buildKeyName($key, ++$this->storage[$level][$key]);
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
}
