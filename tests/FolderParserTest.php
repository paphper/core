<?php

namespace Tests;

use Paphper\FolderParser;
use PHPUnit\Framework\TestCase;

class FolderParserTest extends TestCase
{

    public function testFolderParserWorks()
    {
        $folders = [
            'home/pages',
            'home/pages/blogs/',
            'home/pages/dates/posts',
            'home/this-is-the-long-ass-folder/posts/',
            'home/pages/dates/',
            'home/naren/dates/',
            'home/pages/naren/',
        ];

        $folderParser = new FolderParser($folders);
        $folders = $folderParser->parse();


        $this->assertContains('home/pages/blogs/', $folders);
        $this->assertContains('home/pages/dates/posts', $folders);
        $this->assertContains('home/this-is-the-long-ass-folder/posts/', $folders);
        $this->assertNotContains('home/pages/dates/', $folders);
        $this->assertContains('home/naren/dates/', $folders);
        $this->assertContains('home/pages/naren/', $folders);

    }

    public function testDoesnotBreakWhenPassedEmptyArray()
    {

        $parser = new FolderParser([]);
        $folders = $parser->parse();

        $this->assertSame([], $folders);
    }

    public function testThisWorks()
    {
        $array = [
            'tests/Mocks/build/bios/salam',
            'tests/Mocks/build/bios/naren',
            'tests/Mocks/build/blogs/2020',
            'tests/Mocks/build/non-html',
            'tests/Mocks/build/blogs',
        ];

        $parser = new FolderParser($array);
        $folders = $parser->parse();

        $this->assertContains('tests/Mocks/build/bios/salam', $folders);
        $this->assertContains('tests/Mocks/build/bios/naren', $folders);
        $this->assertContains('tests/Mocks/build/blogs/2020', $folders);
        $this->assertContains('tests/Mocks/build/non-html', $folders);
        $this->assertNotContains('tests/Mocks/build/blogs', $folders);

    }
}
