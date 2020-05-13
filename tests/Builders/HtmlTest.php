<?php

namespace Tests\Builders;

use function Clue\React\Block\await;
use Paphper\Contents\Html;
use Tests\AbstractTestCase;

class HtmlTest extends AbstractTestCase
{
    public function testMetaIsParsedCorrectly()
    {
        $html = new Html($this->config, $this->filesystem, $this->baseDir.'/Mocks/pages/index.html');
        $metaData = await($html->getMetaData(), $this->loop);
        $this->assertSame($metaData->getLayout(), 'index.html');
        $this->assertSame($metaData->get('title'), 'this is a test');
        $this->assertSame(trim($metaData->getBody()), '<div>Hello WOrld</div>');
        $this->assertSame(await($html->getLayoutContent(), $this->loop), $this->getTestLayoutContent());
    }

    private function getTestLayoutContent()
    {
        return
'<html>
<head>
    <title>{title}</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<h1>this is the homepage</h1>
{content}
</body>
</html>
';
    }
}
