<?php

namespace Tests\Builders;


use Paphper\Contents\Md;
use Paphper\FileReader;
use Tests\AbstractTestCase;
use function Clue\React\Block\await;

class MdTest extends AbstractTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testMetaFromMdFilesAreCorrectlyParsed()
    {
        $fileReader = new FileReader($this->filesystem, $this->baseDir . '/Mocks/pages/non-html.md');
        $md = new Md($fileReader);
        $meta = await($md->getMetaData(), $this->loop);

        $this->assertSame(trim($meta->getBody()), '<h1>This is the h1 tag</h1>
<h2>this may be a blog post</h2>
<p>This is the content of the page</p>');
        $this->assertSame($meta->getLayout(), 'blog.html');
    }
}
