<?php

namespace Tests\Contents;

use function Clue\React\Block\await;
use Paphper\Contents\Html;
use Paphper\Contents\MetaParser;
use Tests\AbstractTestCase;

class HtmlTest extends AbstractTestCase
{
    public function testHtmlContentIsSuccessfullyGenerated()
    {
        $meta = new MetaParser($this->config, $this->filesystem, $this->config->getPageBaseFolder().'/index.html');
        $html = new Html($meta);
        $promise = $html->getPageContent()->then(function ($pageContent) {
            $this->assertSame($this->getTestPageContent(), $pageContent);
        });

        await($promise, $this->loop);
    }

    private function getTestPageContent()
    {
        return
'<html>
<head>
    <title>this is a test</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<h1>this is the homepage</h1>


<div>Hello WOrld</div>

</body>
</html>
';
    }
}
