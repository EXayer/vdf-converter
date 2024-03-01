<?php

use EXayer\VdfConverter\UniqueKey\DefaultFormatter;
use EXayer\VdfConverter\VdfConverterConfig;
use PHPUnit\Framework\TestCase;

class VdfConverterConfigTest extends TestCase
{
    public function testUniqueKeyFormatterCreation()
    {
        $config = VdfConverterConfig::create()
            ->uniqueKeyFormatter(DefaultFormatter::class);

        $this->assertEquals(new DefaultFormatter(), $config->getUniqueKeyFormatter());
    }
}