<?php

namespace Tests\Unit\Utils;

use Paphper\Utils\Str;
use PHPUnit\Framework\TestCase;

class StrTest extends TestCase
{
    private $testString = 'narendra chitrakar';

    public function testEndsWith()
    {
        $string = new Str($this->testString);

        $this->assertTrue($string->endsWith('chitrakar'));
        $this->assertTrue($string->endsWith('r'));
        $this->assertFalse($string->endsWith('arend'));


        $newString = new Str('n');
        $newString->startsWith('na');
        $newString->endsWith('na');
    }

    public function testStartsWith()
    {
        $string = new Str($this->testString);

        $this->assertTrue($string->startsWith('naren'));
        $this->assertTrue($string->startsWith('n'));
        $this->assertTrue($string->startsWith('narendra'));
        $this->assertFalse($string->startsWith('arend'));
    }

    public function testReplaceAllWith()
    {
        $string = new Str($this->testString);
        $this->assertSame($string->replaceAllWith('n', ''), 'aredra chitrakar');
    }

    public function testReplaceLastWith()
    {
        $string = new Str('narern.httmlnaren.html');
        $this->assertSame($string->replaceLastWith('.html', ''), 'narern.httmlnaren');

        $string = new Str('blogs/replace.md');
        $this->assertSame($string->replaceLastWith('.md', ''), 'blogs/replace');
    }

    public function testGetBeforeLast()
    {
        $string = new Str('narend/is/the/best/person.jpt');

        $this->assertSame($string->getBeforeLast('/'), 'narend/is/the/best');
        $this->assertSame($string->getBeforeLast('bg'), '');
    }

    public function testGetAfterLast()
    {
        $string = new Str('naren.html');
        $this->assertSame('html', $string->getAfterLast('.'));
        $this->assertSame('', $string->getAfterLast('dhirendra'));
    }


}
