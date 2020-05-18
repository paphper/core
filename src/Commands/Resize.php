<?php

namespace Paphper\Commands;

use function Clue\React\Block\await;
use Intervention\Image\ImageManager;
use Paphper\Config;
use React\EventLoop\LoopInterface;
use React\Filesystem\FilesystemInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Resize extends Command
{
    protected static $defaultName = 'crop';
    private $filesystem;
    private $config;
    private $loop;
    private $manager;

    public function __construct(LoopInterface $loop, FilesystemInterface $filesystem, Config $config, ImageManager $manager)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
        $this->config = $config;
        $this->loop = $loop;
        $this->manager = $manager;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $width = 25;
        $height = 25;
        $promise = $this->filesystem->file($this->config->getAssetsBaseFolder().'/img1.jpg')->getContents()
            ->then(function ($content) {
                $this->manager->make($content)->resize(25, 25)->save($this->config->getAssetsBaseFolder().'/test.jpg');

                return 'done';
            });

        await($promise, $this->loop);
        $this->loop->run();

        return 0;
    }
}
