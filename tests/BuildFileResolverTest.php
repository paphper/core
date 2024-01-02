<?php

namespace Tests;

use Paphper\BuildFileResolver;

class BuildFileResolverTest extends AbstractTestCase
{
    public function testBuildFileAreResolvedCorrectly()
    {
        $resolver = new BuildFileResolver($this->config, $this->config->getPageBaseFolder().'/blog.html');
        $this->assertSame($this->config->getBuildBaseFolder().'/blog', $resolver->getFolder());
        $this->assertSame('/blog', $resolver->getUrlPath());
    }

    public function testForDeepFolder()
    {
        $resolver = new BuildFileResolver($this->config, $this->config->getPageBaseFolder().'/blogs/testing/naren.md');
        $this->assertSame($this->config->getBuildBaseFolder().'/blogs/testing/naren', $resolver->getFolder());
        $this->assertSame('/blogs/testing/naren', $resolver->getUrlPath());
    }

    public function testForMdFolder()
    {
        $resolver = new BuildFileResolver($this->config, $this->config->getPageBaseFolder().'/index.md');
        $this->assertSame($this->config->getBuildBaseFolder(), $resolver->getFolder());
        $this->assertSame('/', $resolver->getUrlPath());
    }

    public function testForBladeFolder()
    {
        $resolver = new BuildFileResolver($this->config, $this->config->getPageBaseFolder().'/index.blade.php');

        $this->assertSame($this->config->getBuildBaseFolder(), $resolver->getFolder());
        $this->assertSame('/', $resolver->getUrlPath());
    }
}
