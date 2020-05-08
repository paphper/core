<?php

namespace Tests\Builders;

use Paphper\Contents\Html;
use Paphper\FileReader;
use Tests\AbstractTestCase;
use function Clue\React\Block\await;

class HtmlTest extends AbstractTestCase
{

    public function testMetaIsParsedCorrectly()
    {
        $fileReader = new FileReader($this->filesystem, $this->baseDir . '/Mocks/pages/index.html');

        $html = new Html($fileReader);
        $metaData = await($html->getMetaData(), $this->loop);
        $this->assertSame($metaData->getLayout(), 'index.html');
        $this->assertSame($metaData->get('title'), 'this is a test');
        $this->assertSame(trim($metaData->getBody()), '<div>Hello WOrld</div>');
    }

}
