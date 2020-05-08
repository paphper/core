<?php

namespace Tests\Builders;

use Paphper\Contents\Factory;
use Paphper\Contents\Html;
use Paphper\Contents\Md;
use Tests\AbstractTestCase;

class FactoryTest extends AbstractTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testHtmlFileIsCorrectlyResolved()
    {
        $html = Factory::create($this->filesystem, 'naren.html');
        $this->assertInstanceOf(Html::class, $html);

        $md = Factory::create($this->filesystem, 'naren.md');
        $this->assertNotInstanceOf(Html::class, $md);

    }

    public function testMdFileIsCorrectlyResolved()
    {
        $html = Factory::create($this->filesystem, 'naren.md');
        $this->assertInstanceOf(Md::class, $html);

        $md = Factory::create($this->filesystem, 'naren.html');
        $this->assertNotInstanceOf(Md::class, $md);

    }


}
