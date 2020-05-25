<?php

namespace Paphper\Commands;

use Paphper\BuildFileResolver;
use Paphper\FileContentResolver;
use Paphper\Responses\Factory as ResponseFactory;
use Paphper\SiteGenerator;
use Paphper\Utils\FileCreator;
use Paphper\Utils\Str;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Server;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Yosymfony\ResourceWatcher\Crc32ContentHash;
use Yosymfony\ResourceWatcher\ResourceCachePhpFile;
use Yosymfony\ResourceWatcher\ResourceWatcher;

class Watch extends Command
{
    public static $contentHashMap = [];
    public static $filesToBuild = [];
    protected static $defaultName = 'dev';
    private $config;
    private $filesystem;
    private $io;
    private $loop;
    private $watcher;
    private $fileContentResolver;
    private $pageResolver;
    private $manager;

    public function __construct($config, $pageResolver, FileContentResolver $fileContentResolver, $filesystem, $loop, $manager)
    {
        parent::__construct();
        $this->config = $config;
        $this->pageResolver = $pageResolver;
        $this->fileContentResolver = $fileContentResolver;
        $this->filesystem = $filesystem;
        $this->loop = $loop;
        $this->manager = $manager;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $port = $this->config->getPort();
        $this->io = $io = new PaphperStyle($input, $output);

        $baseFolder = $this->config->getBuildBaseFolder();

        $silent = $input->getOption('quiet');
        $force = $input->getOption('force');

        if (false === $force && true !== $silent) {
            $answer = $this->io->ask(sprintf('This will delete the folder %s. Are you sure? Type yes to continue.', $baseFolder));
            if (!in_array($answer, ['y', 'ye', 'yes'])) {
                $this->io->section('Skipping. Bye!');

                return 0;
            }
        }

        $folderWatching = [(new Str($this->config->getPageBaseFolder()))->getBeforeLast('/')];
        $this->io->title('Watching for file changes');
        $this->io->table(['watching folder'], [[implode(' and ', $folderWatching)]]);

        $extensionToWatch = array_map(function ($extension) {
            return '*'.$extension;
        }, $this->fileContentResolver->getExtensions());

        $finder = new Finder();
        $finder->files()
            ->name($extensionToWatch)
            ->in($folderWatching);

        $hashContent = new Crc32ContentHash();
        $resourceCache = new ResourceCachePhpFile(getBaseDir().'/path-cache-file.php');
        $this->watcher = new ResourceWatcher($resourceCache, $finder, $hashContent);
        $this->watcher->initialize();

        $generator = new SiteGenerator($this->pageResolver, $this->fileContentResolver, $this->config, $this->filesystem, $this->loop, $this->manager);
        $generator->build();

        $this->loop->addPeriodicTimer(1, function () {
            $result = $this->watcher->findChanges();
            $changedFiles = $result->getUpdatedFiles();
            $pageFiles = array_filter($changedFiles, function ($filename) {
                return (new Str($filename))->startsWith($this->config->getPageBaseFolder());
            });

            if (empty($changedFiles)) {
                return;
            }

            if (0 === count($pageFiles)) {
                $pagesToBuild = self::$filesToBuild;
            } else {
                $pagesToBuild = $pageFiles;
            }

            foreach ($pagesToBuild as $filename) {
                if (!in_array($filename, self::$filesToBuild)) {
                    self::$filesToBuild[] = $filename;
                }

                $htmlGenerator = $this->fileContentResolver->resolve($filename);
                $htmlGenerator->getPageContent()
                    ->then(function ($content) use ($filename) {
                        $buildFile = new BuildFileResolver($this->config, $filename);
                        (new FileCreator($this->filesystem, $buildFile->getName(), $content))->writeFile()
                            ->then(function () use ($buildFile) {
                                $this->io->text(sprintf('%s build', $buildFile->getName()));
                            });
                    });
            }
        });

        $server = new Server(function (ServerRequestInterface $request) use (&$firstBuild) {
            $response = ResponseFactory::create($request, $this->config, $this->filesystem, $this->manager);

            return $response->toResponse();
        });

        $this->io->text('trying to open browser. Serving static server at 0.0.0.0:'.$port);

        try {
            $this->openBrowser($port);
        } catch (\Exception $exception){
            $this->io->text(sprintf('failed opening browser. open at 0.0.0.0:%s in a browser', $port));
        }

        $socket = new \React\Socket\Server('0.0.0.0:'.$port, $this->loop);
        $server->listen($socket);

        $this->loop->run();

        return 0;
    }

    public function openBrowser($port)
    {
        switch (PHP_OS_FAMILY) {
            case 'Linux':
                exec('xdg-open http://localhost:'.$port);
                break;
            case 'Windows':
                exec('start http://localhost:'.$port);
                break;
            default:
                exec('open http://localhost:'.$port);
        }
    }

    protected function configure()
    {
        $this->addOption('force', '-f', InputOption::VALUE_OPTIONAL, 'Force Build?', false);
    }
}
