<?php

namespace Paphper\Commands;

use Paphper\SiteGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Build extends Command
{
    protected static $defaultName = 'build';
    private $config;
    private $pageResolvers;
    private $fileContentResolver;
    private $filesystem;
    private $loop;
    private $io;

    public function __construct($config, $pageResolvers, $fileContentResolver, $filesystem, $loop)
    {
        parent::__construct();
        $this->config = $config;
        $this->pageResolvers = $pageResolvers;
        $this->fileContentResolver = $fileContentResolver;
        $this->filesystem = $filesystem;
        $this->loop = $loop;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new PaphperStyle($input, $output);
        $generator = new SiteGenerator($this->pageResolvers, $this->fileContentResolver, $this->config, $this->filesystem, $this->loop, $io);
        $generator->build();

        return 0;
    }
}
