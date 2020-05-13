<?php

namespace Tests\Builders;

use function Clue\React\Block\await;
use Paphper\Contents\Md;
use Tests\AbstractTestCase;

class MdTest extends AbstractTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testMetaFromMdFilesAreCorrectlyParsed()
    {
        $md = new Md($this->config, $this->filesystem, $this->baseDir.'/Mocks/pages/non-html.md');
        $meta = await($md->getMetaData(), $this->loop);

        $this->assertSame(trim($meta->getBody()), '<h1>This is the h1 tag</h1>
<h2>this may be a blog post</h2>
<p>This is the content of the page</p>');
        $this->assertSame($meta->getLayout(), 'blog.html');

        $this->assertSame($this->getTestLayout(), await($md->getLayoutContent(), $this->loop));
    }

    private function getTestLayout()
    {
        return
'<html>
<head>
    <title>{title}</title>
</head>
<body>
{content}
</body>
</html>
';
    }
}
