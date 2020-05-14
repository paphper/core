<?php

namespace Tests;

use Paphper\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testConfigWorks()
    {
        $pageDir = '/Mocks/pages/';
        $layoutDir = '/Mocks/layouts/';
        $buildDir = '/Mocks/build/';

        $config = new Config([
            'pages_dir' => $pageDir,
            'layout_dir' => $layoutDir,
            'build_dir' => $buildDir,
        ]);

        $this->assertSame($pageDir, $config->getPageBaseFolder());
        $this->assertSame($layoutDir, $config->getLayoutBaseFolder());
        $this->assertSame($buildDir, $config->getBuildBaseFolder());
    }

    public function testDefaultOptionsWork()
    {
        $config = new Config();
        $this->assertSame('', $config->getPageBaseFolder());
        $this->assertSame('', $config->getLayoutBaseFolder());
        $this->assertSame('', $config->getBuildBaseFolder());
    }
}
