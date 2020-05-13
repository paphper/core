<?php

namespace Tests\Builders;

use function Clue\React\Block\await;
use Paphper\Config;
use Paphper\Contents\Html;
use Paphper\Contents\Md;
use Paphper\HtmlGenerator;
use Symfony\Component\DomCrawler\Crawler;
use Tests\AbstractTestCase;

class PageBuilderTest extends AbstractTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testHtmlFileIsSuccessfullyBuilt()
    {
        $configData = include getBaseDir().'/config.php';

        $config = new Config($configData);

        $html = new Html($this->config, $this->filesystem, $config->getPageBaseFolder().'/index.html');

        $builder = new HtmlGenerator($config, $html);
        $result = $builder->getHtml()->then(function ($outputContent) {
            $domCrawler = new Crawler($outputContent);
            //title is correct in the generate html
            $this->assertSame('this is a test', $domCrawler->filterXPath('//title')->getNode(0)->textContent);
            $this->assertSame('this is the homepage', $domCrawler->filterXPath('//h1')->getNode(0)->textContent);
            $this->assertSame('Hello WOrld', $domCrawler->filterXPath('//div')->getNode(0)->textContent);
        });

        await($result, $this->loop);
    }

    public function testMdFileIsSuccessfullyBuilt()
    {
        $configData = include getBaseDir().'/config.php';

        $config = new Config($configData);

        $html = new Md($this->config, $this->filesystem, $config->getPageBaseFolder().'/non-html.md');
        $builder = new HtmlGenerator($config, $html);
        $result = $builder->getHtml()->then(function ($outputContent) {
            $domCrawler = new Crawler($outputContent);
//          title is correct in the generate html
            $this->assertSame('Blog Title', $domCrawler->filterXPath('//title')->getNode(0)->textContent);
            $this->assertSame('This is the h1 tag', $domCrawler->filterXPath('//h1')->getNode(0)->textContent);
            $this->assertSame('this may be a blog post', $domCrawler->filterXPath('//h2')->getNode(0)->textContent);
            $this->assertSame('This is the content of the page', $domCrawler->filterXPath('//p')->getNode(0)->textContent);
        });

        await($result, $this->loop);
    }
}
