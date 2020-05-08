<?php

namespace Tests;

use Paphper\FileReader;
use function Clue\React\Block\await;

class FileReaderTest extends AbstractTestCase
{

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testFileReaderCorrectlyReadsFile()
    {
        $filename = $this->baseDir . '/Mocks/pages/index.html';
        $fileReader = new FileReader($this->filesystem, $filename);
        $content = await($fileReader->getContent(), $this->loop);

        $this->assertSame($content, file_get_contents($filename));
    }
}
