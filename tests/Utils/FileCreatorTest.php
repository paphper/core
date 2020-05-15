<?php

namespace Tests\Utils;

use function Clue\React\Block\await;
use Paphper\Utils\FileCreator;
use Tests\AbstractTestCase;

class FileCreatorTest extends AbstractTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->deleteBuildFolder();
        mkdir($this->config->getBuildBaseFolder());
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->deleteBuildFolder();
    }

    public function testFileCreationCreatesFileAndNecessaryFolder()
    {
        $content = 'this is the content';
        $filename = $this->config->getBuildBaseFolder().'/blogs/index.html';
        $creator = new FileCreator($this->filesystem, $filename, $content);

        $promise = $creator->writeFile()
            ->then(function () use ($content, $filename) {
                $this->assertSame(file_get_contents($filename), $content);
            });

        await($promise, $this->loop);
    }

    //skipped because phpunit is throwing too many files open
//    public function testFileCreatorOverwritesContent()
//    {
//        $content = 'this is the content';
//
//        $filename = $this->config->getBuildBaseFolder().'/index.html';
//
//        file_put_contents($filename, $content . ' plus something more');
//        $creator = new FileCreator($this->filesystem, $filename, $content);
//
//        $promise = $creator->writeFile()
//            ->then(function () use ($content, $filename) {
//                $this->assertSame(file_get_contents($filename), $content);
//            });
//
//        await($promise, $this->loop);
//    }
}
