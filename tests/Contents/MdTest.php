<?php

namespace Tests\Contents;

use function Clue\React\Block\await;
use Paphper\Contents\Md;
use Paphper\Parsers\PaperTagParser;
use Tests\AbstractTestCase;

class MdTest extends AbstractTestCase
{
    public function testMdContentIsSuccessfullyGenerated()
    {
        $meta = new PaperTagParser($this->config, $this->filesystem, $this->config->getPageBaseFolder().'/non-html.md');
        $html = new Md($meta);
        $promise = $html->getPageContent()->then(function ($pageContent) {
            $this->assertSame($this->getTestContent(), $pageContent);
        });

        await($promise, $this->loop);
    }

    private function getTestContent()
    {
        return
            '<html>
<head>
    <title>Blog Title</title>
</head>
<body>
<h1>This is the h1 tag</h1>
<h2>this may be a blog post</h2>
<p>This is the content of the page</p>

</body>
</html>
';
    }
}
