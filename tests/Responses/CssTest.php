<?php

namespace Tests\Responses;

use function Clue\React\Block\await;
use Paphper\Responses\Asset;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;
use RingCentral\Psr7\Uri;
use Tests\AbstractTestCase;

class CssTest extends AbstractTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        @mkdir($this->config->getBuildBaseFolder());
    }

    public function tearDown(): void
    {
        parent::setUp();
        $this->deleteBuildFolder();
    }

    public function testCssResponseIsReadFromBuildFolder()
    {
        $url = $this->createMock(Uri::class);
        $url->method('getPath')->willReturn('/style.css');
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($url);
        $request->method('getHeader')->willReturn([]);

        $content = 'body{color:green}';

        file_put_contents($this->config->getBuildBaseFolder().'/style.css', $content);

        $html = new Asset($request, $this->config, $this->filesystem, $this->imageManager);
        $response = $html->toResponse();

        $promise = $response->then(function (Response $response) use ($content) {
            $this->assertSame($content, trim($response->getBody()->getContents()));
            $this->assertSame($response->getHeader('Content-Type'), ['text/css']);
        });

        await($promise, $this->loop);
    }

    public function testCssIsCopiedFromAssetFolderIfDoesNotExistInBuildFolder()
    {
        $url = $this->createMock(Uri::class);
        $url->method('getPath')->willReturn('/css/style.css');
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($url);
        $request->method('getHeader')->willReturn([]);

        $content =
            'body {
    background: green;
}';

        file_put_contents($this->config->getBuildBaseFolder().'/style.css', $content);

        $html = new Asset($request, $this->config, $this->filesystem, $this->imageManager);
        $response = $html->toResponse();

        $promise = $response->then(function (Response $response) use ($content) {
            $this->assertSame($content, trim($response->getBody()->getContents()));
            $this->assertSame($response->getHeader('Content-Type'), ['text/css']);
        });

        await($promise, $this->loop);
    }
}
