<?php

namespace Paphper\Commands;

use Paphper\Config;
use Paphper\SiteGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Build extends Command
{
    protected static $defaultName = 'build';
    private $config;
    private $pageResolvers;
    private $fileContentResolver;
    private $filesystem;
    private $loop;
    private $manager;

    public function __construct(Config $config, $pageResolvers, $fileContentResolver, $filesystem, $loop, $manager)
    {
        parent::__construct();
        $this->config = $config;
        $this->pageResolvers = $pageResolvers;
        $this->fileContentResolver = $fileContentResolver;
        $this->filesystem = $filesystem;
        $this->loop = $loop;
        $this->manager = $manager;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new PaphperStyle($input, $output);

        $silent = $input->getOption('quiet');
        $force = $input->getOption('force');
        $baseFolder = $this->config->getBuildBaseFolder();

        if (false === $force && true !== $silent) {
            $answer = $io->ask(sprintf('This will delete the folder %s. Are you sure? Type yes to continue.', $baseFolder));
            if (!in_array($answer, ['y', 'ye', 'yes'])) {
                $io->section('Skipping. Bye!');

                return 0;
            }
        }
        $generator = new SiteGenerator($this->pageResolvers, $this->fileContentResolver, $this->config, $this->filesystem, $this->loop, $this->manager, $io);
        $generator->build();

        return 0;
    }

    protected function configure()
    {
        $this->addOption('force', '-f', InputOption::VALUE_OPTIONAL, 'Force Build?', false);
    }
}
