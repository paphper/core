<?php

namespace Tests\Responses;

use function Clue\React\Block\await;
use Paphper\Responses\Html;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;
use RingCentral\Psr7\Uri;
use Tests\AbstractTestCase;

class HtmlTest extends AbstractTestCase
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

    public function testHtmlResponse()
    {
        $url = $this->createMock(Uri::class);
        $url->method('getPath')->willReturn('/');
        $url->method('__toString')->willReturn('http://localhost/');
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($url);
        $request->method('getHeader')->willReturn([]);

        $content = 'this is the content';

        file_put_contents($this->config->getBuildBaseFolder().'/index.html', $content);

        $html = new Html($request, $this->config, $this->filesystem);
        $response = $html->toResponse();

        $promise = $response->then(function (Response $response) use ($content) {
            $this->assertTrue(0 === strpos(trim($response->getBody()->getContents()), $content));
            $this->assertSame($response->getHeader('Content-Type'), ['text/html']);
        });

        await($promise, $this->loop);
    }
}
