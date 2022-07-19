<?php

namespace Test\DevAnime;

use DevAnime\Util;
use WPTest\Test\TestCase;

class UtilTest extends TestCase
{
    function test_it_singularizes_a_word()
    {
        $this->assertEquals('category', Util::singularize('categories'));
        $this->assertEquals('box', Util::singularize('boxes'));
        $this->assertEquals('tag', Util::singularize('tags'));
        $this->assertEquals('geese', Util::singularize('geese'));
        $this->assertEquals('to', Util::singularize('to'));
        $this->assertEquals('', Util::singularize(''));
    }
}