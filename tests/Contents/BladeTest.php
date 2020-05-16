<?php


namespace Tests\Contents;


use Paphper\Contents\Blade;
use Paphper\Utils\Str;
use Tests\AbstractTestCase;
use function Clue\React\Block\await;

class BladeTest extends AbstractTestCase
{

    public function testThisWorks()
    {
        $baseFolder = $this->config->getPageBaseFolder();
        $lBlade= new \Jenssegers\Blade\Blade( (new Str($baseFolder))->getBeforeLast('/'), $this->config->getCacheDir());
        $blade = new Blade($this->config, $lBlade, $this->filesystem, $this->config->getPageBaseFolder() . '/blade.blade.php');

        $content = await($blade->getPageContent(), $this->loop);

        $this->assertSame($this->getBladeContent(), $content);
    }

    private function getBladeContent()
    {
        return
'<html>

<head>
    <title>this is a test</title>
</head>

<body>
hello world


    this is a different test
</body>
</html>
';
    }
}
