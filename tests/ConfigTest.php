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
        $port = 3000;

        $config = new Config([
            'pages_dir' => $pageDir,
            'layout_dir' => $layoutDir,
            'build_dir' => $buildDir,
            'port' => $port,
        ]);

        $this->assertSame($pageDir, $config->getPageBaseFolder());
        $this->assertSame($layoutDir, $config->getLayoutBaseFolder());
        $this->assertSame($buildDir, $config->getBuildBaseFolder());
        $this->assertSame($port, $config->getPort());
    }

    public function testDefaultOptionsWork()
    {
        $config = new Config();
        $this->assertSame('', $config->getPageBaseFolder());
        $this->assertSame('', $config->getLayoutBaseFolder());
        $this->assertSame('', $config->getBuildBaseFolder());
    }
}
