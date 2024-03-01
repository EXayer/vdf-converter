<?php

namespace EXayer\VdfConverter;

use EXayer\VdfConverter\UniqueKey\DefaultFormatter;
use EXayer\VdfConverter\UniqueKey\Formatter;

class VdfConverterConfig
{
    /**
     * @var string
     */
    private $uniqueKeyFormatter = DefaultFormatter::class;

    /**
     * @return self
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * @param string $formatterClassName
     *
     * @return $this
     */
    public function uniqueKeyFormatter(string $formatterClassName): self
    {
        $this->uniqueKeyFormatter = $formatterClassName;

        return $this;
    }

    /**
     * @return Formatter
     */
    public function getUniqueKeyFormatter(): Formatter
    {
        return new $this->uniqueKeyFormatter;
    }
}
