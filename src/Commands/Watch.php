<?php

namespace Paphper\Commands;

use Paphper\BuildFileResolver;
use Paphper\Responses\Factory as ResponseFactory;
use Paphper\SiteGenerator;
use Paphper\Utils\FileCreator;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Server;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Yosymfony\ResourceWatcher\Crc32ContentHash;
use Yosymfony\ResourceWatcher\ResourceCachePhpFile;
use Yosymfony\ResourceWatcher\ResourceWatcher;

class Watch extends Command
{
    public static $contentHashMap = [];
    protected static $defaultName = 'dev';
    private $config;
    private $filesystem;
    private $io;
    private $loop;
    private $watcher;
    private $changedFiles = [];
    private $fileContentResolver;
    private $pageResolver;
    private $manager;

    public function __construct($config, $pageResolver, $fileContentResolver, $filesystem, $loop, $manager)
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
        $this->io = $io = new SymfonyStyle($input, $output);

        $folderWatching = [$this->config->getPageBaseFolder()];
        $this->io->title('Watching for file changes');
        $this->io->table(['watching folder'], [[implode(' and ', $folderWatching)]]);

        $finder = new Finder();
        $finder->files()
            ->name(['*.html', '*.md', '*.blade.php'])
            ->in($folderWatching);

        $hashContent = new Crc32ContentHash();
        $resourceCache = new ResourceCachePhpFile(getBaseDir().'/path-cache-file.php');
        $this->watcher = new ResourceWatcher($resourceCache, $finder, $hashContent);
        $this->watcher->initialize();

        $generator = new SiteGenerator($this->pageResolver, $this->fileContentResolver, $this->config, $this->filesystem, $this->loop, $this->manager);
        $generator->build();

        $this->loop->addPeriodicTimer(1, function () {
            $result = $this->watcher->findChanges();
            $this->changedFiles = $changedFiles = $result->getUpdatedFiles();
            foreach ($changedFiles as $filename) {
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

        $this->openBrowser($port);

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
}
