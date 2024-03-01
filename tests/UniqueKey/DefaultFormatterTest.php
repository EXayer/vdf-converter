<?php

namespace EXayer\VdfConverter\Tests\UniqueKey;

use EXayer\VdfConverter\UniqueKey\DefaultFormatter;
use PHPUnit\Framework\TestCase;

class DefaultFormatterTest extends TestCase
{
    public function testBuildName()
    {
        $formatter = new DefaultFormatter();
        $formattedKey = $formatter->buildKeyName('sample_key', 9);

        $this->assertEquals($formattedKey, 'sample_key__[9]');
    }
}