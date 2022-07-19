<?php

namespace DevAnime\Support;

use WPTest\Test\TestCase;

class TestFlash extends TestCase
{
    function test_it_creates_a_flashed_value()
    {
        Flash::create('some value', 'test');
        $this->assertEquals(
            'some value',
            get_transient('test_0')
        );
        Flash::create(['an array', 'of data'], 'another_prefix');
        $this->assertEquals(
            ['an array', 'of data'],
            get_transient('another_prefix_0')
        );
        wp_set_current_user(1);
        Flash::create('some user value', 'test');
        $this->assertEquals(
            'some user value',
            get_transient('test_1')
        );
    }

    function test_it_gets_a_flashed_value()
    {
        Flash::create('some value', 'test');
        $this->assertEquals('some value', Flash::get('test'));
        $this->assertEmpty(Flash::get('test'));
        $this->assertEmpty(get_transient('test_0'));

        Flash::create(['an array', 'of data'], 'another_prefix');
        $this->assertEmpty(Flash::get('test'));
        $this->assertEquals(['an array', 'of data'], Flash::get('another_prefix'));
    }
}
