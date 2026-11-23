<?php

namespace TCG\Voyager\Tests\Unit;

use Carbon\Carbon;
use TCG\Voyager\Tests\TestCase;

class HelpersTest extends TestCase
{
    public function testJsonDecodeReturnsNullForEmptyValues()
    {
        $this->assertNull(voyager_json_decode(null));
        $this->assertNull(voyager_json_decode(''));
        $this->assertNull(voyager_json_decode(null, true));
    }

    public function testJsonDecodeDecodesStrings()
    {
        $this->assertEquals(['a', 'b'], voyager_json_decode('["a","b"]'));
        $this->assertEquals((object) ['x' => 1], voyager_json_decode('{"x":1}'));
        $this->assertSame(['x' => 1], voyager_json_decode('{"x":1}', true));
    }

    public function testJsonDecodeNormalizesCastValues()
    {
        $files = voyager_json_decode([['download_link' => 'a.pdf', 'original_name' => 'a.pdf']]);
        $this->assertSame('a.pdf', $files[0]->download_link);

        $this->assertSame(['x' => 1], voyager_json_decode((object) ['x' => 1], true));
    }

    public function testDisplayValueEncodesArraysAndPlainObjects()
    {
        $this->assertSame('{"key":"значение","url":"a/b"}', voyager_display_value(['key' => 'значение', 'url' => 'a/b']));
        $this->assertSame('{"x":1}', voyager_display_value((object) ['x' => 1]));
    }

    public function testDisplayValueKeepsScalarsAndStringables()
    {
        $this->assertSame('text', voyager_display_value('text'));
        $this->assertNull(voyager_display_value(null));

        $date = Carbon::parse('2026-01-01');
        $this->assertSame($date, voyager_display_value($date));
    }
}
