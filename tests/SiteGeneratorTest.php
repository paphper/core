<?php

namespace Tests;

use Paphper\Config;
use Paphper\SiteGenerator;

class SiteGeneratorTest extends AbstractTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->deleteBuildFolder();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->deleteBuildFolder();
    }

    public function testSiteGeneration()
    {
        $siteGenerator = new SiteGenerator($this->config, $this->filesystem, $this->loop);
        $siteGenerator->build();

        $this->assertTrue(file_exists($this->baseDir . '/Mocks/build/non-html/index.html'));
        $this->assertTrue(file_exists($this->baseDir . '/Mocks/build/index.html'));
        $this->assertTrue(file_exists($this->baseDir . '/Mocks/build/blogs/index.html'));
        $this->assertTrue(file_exists($this->baseDir . '/Mocks/build/blogs/2020/index.html'));
        $this->assertTrue(file_exists($this->baseDir . '/Mocks/build/images/img3.jpg'));
        $this->assertTrue(file_exists($this->baseDir . '/Mocks/build/img1.jpg'));
        $this->assertTrue(file_exists($this->baseDir . '/Mocks/build/css/style.css'));
    }

    public function testImageGenerationIsStoppedIfTheAssetsFolderIsNotDefined()
    {
        $config = new Config([
            'pages_dir' => getBaseDir() . '/Mocks/pages',
            'layout_dir' => getBaseDir() . '/Mocks/layouts',
            'build_dir' => getBaseDir() . '/Mocks/build'
        ]);

        $siteGenerator = new SiteGenerator($config, $this->filesystem, $this->loop);
        $siteGenerator->build();
        $this->assertTrue(file_exists($this->baseDir . '/Mocks/build/non-html/index.html'));
        $this->assertTrue(file_exists($this->baseDir . '/Mocks/build/index.html'));
        $this->assertTrue(file_exists($this->baseDir . '/Mocks/build/blogs/index.html'));
        $this->assertTrue(file_exists($this->baseDir . '/Mocks/build/blogs/2020/index.html'));
        $this->assertTrue(!file_exists($this->baseDir . '/Mocks/build/img1.jpg'));
    }
}
