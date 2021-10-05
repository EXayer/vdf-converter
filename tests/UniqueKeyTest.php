<?php

namespace EXayer\VdfConverter\Tests;

use EXayer\VdfConverter\UniqueKey;
use PHPUnit\Framework\TestCase;

class UniqueKeyTest extends TestCase
{
    /**
     * @param array $initStorage
     * @param array $levelsToClear
     * @param array $expected
     * @dataProvider storageDataProvider
     */
    public function testStorage(array $initStorage, array $levelsToClear, array $expected)
    {
        $uniqueKey = new UniqueKey();

        foreach ($initStorage as $pairs) {
            foreach ($pairs as $level => $key) {
                $uniqueKey->get($level, $key);
            }
        }

        foreach ($levelsToClear as $level) {
            $uniqueKey->clear($level);
        }

        $newKey = $uniqueKey->get($expected[0], $expected[1]);

        $this->assertEquals($newKey, $expected[2]);
    }

    public function storageDataProvider()
    {
        return [
            // level => key; levels to clear; level, key, expected key
            [
                [],
                [],
                [1, 'key', 'key']
            ],
            [
                [[1 => 'key'], [1 => 'key']],
                [],
                [1, 'key', 'key__3']
            ],
            [
                [[1 => 'key'], [1 => 'key'], [2 => 'key'], [3 => 'key']],
                [1],
                [1, 'key', 'key']
            ],
            [
                [[1 => 'key'], [2 => 'key'], [3 => 'key']],
                [2, 3],
                [1, 'key', 'key__2']
            ],
        ];
    }
}
