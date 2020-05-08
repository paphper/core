<?php

namespace Paphper\Watcher;

use Paphper\Config;
use Paphper\Utils\Str;
use Symfony\Component\Console\Style\SymfonyStyle;

class FileChange
{
    protected $clients;
    private $io;
    private $config;

    public function __construct(SymfonyStyle $io, Config $config)
    {
        $this->clients = new \SplObjectStorage();
        $this->io = $io;
        $this->config = $config;
    }

    public function onOpen() {

        $this->io->title('Connected to the browser');
        $headers = ['#','Watching Folders'];
        $rows = [];
        $folders = [getBaseDir() . '/Mocks/pages', getBaseDir() . '/Mocks/layouts'];
        foreach ($folders as $key => $folder) {
            $rows[] = [($key + 1), $folder];
        }

        $this->io->table($headers, $rows);
    }


    public final function notifyFileBuilt(string $filename, string $content)
    {
//        $filename = $this->getRelativeFilename($filename);
        $message = sprintf('%s built', $filename);
        $this->io->block($message);
    }

    public final function notifyFileChange(string $filename)
    {
        $this->io->block(sprintf('%s changed.', $this->getRelativeFilename($filename)));
        $this->io->text('building...');
    }

    private function getRelativeFilename(string $filename): string
    {
        $filename = new Str($filename);
        $filename =  new Str($filename->replaceAllWith($this->config->getPageBaseFolder(), ''));
        return $filename->replaceAllWith($this->config->getBuildBaseFolder(), '');
    }

}
