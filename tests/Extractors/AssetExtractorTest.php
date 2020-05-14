<?php

namespace Tests\Extractors;

use function Clue\React\Block\await;
use Paphper\Extractors\AssetExtractor;
use Tests\AbstractTestCase;

class AssetExtractorTest extends AbstractTestCase
{
    public function testAssetsAreCorrectlyExtractedFromImgTags()
    {
        $test = $this->filesystem->file($this->baseDir.'/Mocks/html-with-images.html')
            ->getContents()
            ->then(function ($content) {
                $assetExtractor = new AssetExtractor($content);
                $this->assertContains('img1.jpg', $assetExtractor->getAssets());
                $this->assertContains('img2.jpg', $assetExtractor->getAssets());
                $this->assertContains('figure.png', $assetExtractor->getAssets());
            });

        await($test, $this->loop);
    }

    public function testOnlyUniqueAssetsAreFetched()
    {
        $test = $this->filesystem->file($this->baseDir.'/Mocks/html-with-images.html')
            ->getContents()
            ->then(function ($content) {
                $assetExtractor = new AssetExtractor($content);
                $this->assertCount(10, $assetExtractor->getAssets());
            });

        await($test, $this->loop);
    }

    public function testBackgroundAssetsAreFetched()
    {
        $test = $this->filesystem->file($this->baseDir.'/Mocks/html-with-images.html')
            ->getContents()
            ->then(function ($content) {
                $assetExtractor = new AssetExtractor($content);
                $this->assertContains('narendra.bmp', $assetExtractor->getAssets());
                $this->assertContains('saru.png', $assetExtractor->getAssets());
                $this->assertContains('images/hel-lo/saru.png', $assetExtractor->getAssets());
            });

        await($test, $this->loop);
    }

    public function testCssLinksAreFetched()
    {
        $test = $this->filesystem->file($this->baseDir.'/Mocks/html-with-images.html')
            ->getContents()
            ->then(function ($content) {
                $assetExtractor = new AssetExtractor($content);
                $this->assertContains('/style.css', $assetExtractor->getAssets());
                $this->assertContains('/base/styles/big.style.css', $assetExtractor->getAssets());
            });

        await($test, $this->loop);
    }

    public function testExternalLinksAreIgnored()
    {
        $test = $this->filesystem->file($this->baseDir.'/Mocks/html-with-images.html')
            ->getContents()
            ->then(function ($content) {
                $assetExtractor = new AssetExtractor($content);
                $this->assertContains('/style.css', $assetExtractor->getAssets());
                $this->assertContains('/base/styles/big.style.css', $assetExtractor->getAssets());
                $this->assertNotContains('https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css', $assetExtractor->getAssets());
                $this->assertNotContains('https://unpkg.com/logo.png', $assetExtractor->getAssets());
                $this->assertNotContains('https://unpkg.com/img-logo.png', $assetExtractor->getAssets());
                $this->assertNotContains('https://unpkg.com/logo.png', $assetExtractor->getAssets());
            });

        await($test, $this->loop);
    }
}
