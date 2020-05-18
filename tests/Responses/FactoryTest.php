<?php

namespace Tests\Responses;

use Paphper\Responses\Asset;
use Paphper\Responses\Factory;
use Paphper\Responses\Html;
use Psr\Http\Message\ServerRequestInterface;
use RingCentral\Psr7\Uri;
use Tests\AbstractTestCase;

class FactoryTest extends AbstractTestCase
{
    public function testFactoryReturnsHtmlForRegularUri()
    {
        $url = $this->createMock(Uri::class);
        $url->method('getPath')->willReturn('/');
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($url);

        $factory = Factory::create($request, $this->config, $this->filesystem, $this->imageManager);
        $this->assertInstanceOf(Html::class, $factory);
    }

    public function testFactoryReturnsAssetForCss()
    {
        $url = $this->createMock(Uri::class);
        $url->method('getPath')->willReturn('/naren.css');
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($url);

        $factory = Factory::create($request, $this->config, $this->filesystem, $this->imageManager);
        $this->assertInstanceOf(Asset::class, $factory);
    }

    public function testFactoryReturnsAssetForImage()
    {
        $url = $this->createMock(Uri::class);
        $url->method('getPath')->willReturn('/naren.jpg');
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($url);

        $factory = Factory::create($request, $this->config, $this->filesystem, $this->imageManager);
        $this->assertInstanceOf(Asset::class, $factory);
    }
}
