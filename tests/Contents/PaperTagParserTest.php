<?php

namespace Tests\Contents;

use function Clue\React\Block\await;
use Paphper\Parsers\PaperTagParser;
use React\Promise\PromiseInterface;
use Tests\AbstractTestCase;

class PaperTagParserTest extends AbstractTestCase
{
    public function testMetaParserWorks()
    {
        $meta = new PaperTagParser($this->config, $this->filesystem, $this->config->getPageBaseFolder().'/index.html');

        $promise = $meta->process()->then(function () use ($meta) {
            $this->assertSame('index.html', $meta->getLayout());
            $this->assertSame('this is a test', $meta->get('title'));
            $this->assertInstanceOf(PromiseInterface::class, $meta->getLayoutContent());
            $this->assertSame('<div>Hello WOrld</div>', trim($meta->getBody()));
//            $this->assertSame($meta->getContent());
        });

        await($promise, $this->loop);
    }
}
