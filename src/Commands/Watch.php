<?php

namespace Paphper\Commands;

use Paphper\BuildFileResolver;
use Paphper\HtmlGenerator;
use Paphper\SiteGenerator;
use Paphper\Utils\FileCreator;
use Paphper\Utils\Str;
use Paphper\Watcher\FileChange;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Filesystem\Filesystem;
use React\Http\Server;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Paphper\Config;
use Symfony\Component\Finder\Finder;
use Yosymfony\ResourceWatcher\Crc32ContentHash;
use Yosymfony\ResourceWatcher\ResourceWatcher;
use Yosymfony\ResourceWatcher\ResourceCachePhpFile;
use Paphper\Responses\Factory as ResponseFactory;

class Watch extends Command
{
    protected static $defaultName = 'dev';
    private $message;
    private $config;
    private $filesystem;
    private $io;
    private $loop;
    private $watcher;
    private $changedFiles = [];
    public static $contentHashMap = [];

    public function __construct(Config $config)
    {
        parent::__construct();
        $this->config = $config;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $port = $this->config->getPort();
        $this->io = $io = new SymfonyStyle($input, $output);
        $this->message = new FileChange($io, $this->config);;
        $this->loop = Factory::create();
        $this->filesystem = Filesystem::create($this->loop);

        $folderWatching = [$this->config->getPageBaseFolder()];
        $this->io->title('Watching for file changes');
        $this->io->table(['watching folder'], [[implode(' and ', $folderWatching)]]);

        $finder = new Finder();
        $finder->files()
            ->name('*.html')
            ->name('*.md')
            ->in($folderWatching);

        $hashContent = new Crc32ContentHash();
        $resourceCache = new ResourceCachePhpFile(getBaseDir() . '/path-cache-file.php');
        $this->watcher = new ResourceWatcher($resourceCache, $finder, $hashContent);
        $this->watcher->initialize();

        $siteGenerator = new SiteGenerator($this->config, $this->filesystem, $this->loop);
        $siteGenerator->build();

        $this->loop->addPeriodicTimer(1, function () {
            $result = $this->watcher->findChanges();
            $this->changedFiles = $changedFiles = $result->getUpdatedFiles();
            foreach ($changedFiles as $file) {
                $generator = new HtmlGenerator($this->config, $this->filesystem, $file);
                $this->message->notifyFileChange($file);
                $generator->getHtml()->then(function ($content) use ($file) {
                    $buildFile = (new BuildFileResolver($this->config, $file))->getName();
                    $path = str_replace($this->config->getBuildBaseFolder(), '', (new Str($buildFile))->removeLast('/index.html'));

                    unset(self::$contentHashMap[$path]);
                    $this->message->notifyFileChange($file);

                    (new FileCreator($this->filesystem, $buildFile, $content))->writeFile()
                        ->then(function () use ($buildFile) {
                            $this->io->text(sprintf('%s build', $buildFile));
                        });
                });
            }
        });

        $server = new Server(function (ServerRequestInterface $request) use (&$firstBuild) {

            $response = ResponseFactory::create($request, $this->config, $this->filesystem );
            return $response->toResponse();
        });

        $this->openBrowser($port);

        $socket = new \React\Socket\Server('127.0.0.1:' . $port, $this->loop);
        $server->listen($socket);

        $this->loop->run();

        return 0;
    }

    public function openBrowser($port)
    {
        switch (PHP_OS_FAMILY) {
            case 'Linux':
                exec('xdg-open http://localhost:' . $port);
                break;
            case 'Windows':
                exec('start http://localhost:' . $port);
                break;
            default:
                exec('open http://localhost:' . $port);
        }

    }
}
