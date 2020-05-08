<?php


namespace Tests;


use Paphper\Config;
use PHPUnit\Framework\TestCase;
use React\EventLoop\Factory;
use React\Filesystem\Filesystem;
use function Clue\React\Block\await;

class AbstractTestCase extends TestCase
{

    protected $filesystem;
    protected $loop;
    protected $baseDir;
    protected $config;

    public function setUp(): void
    {
        $this->loop = Factory::create();
        $this->filesystem = Filesystem::create($this->loop);
        $this->baseDir = getBaseDir();
        $configData = include getBaseDir() . '/config.php';
        $this->config = new Config($configData);
    }

    public function deleteBuildFolder()
    {
        $promise = $this->filesystem->dir($this->config->getBuildBaseFolder())
            ->stat()
            ->then(function (){
                return $this->filesystem->dir($this->config->getBuildBaseFolder())->removeRecursive();
            }, function (\Exception $exception){

            });
        await($promise, $this->loop);
    }

}
