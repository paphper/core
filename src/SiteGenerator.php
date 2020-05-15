<?php

namespace Paphper;

use function Clue\React\Block\await;
use Paphper\Extractors\AssetExtractor;
use Paphper\Contracts\PageResolverInterface;
use Paphper\Utils\Str;
use React\EventLoop\LoopInterface;
use React\Filesystem\FilesystemInterface;
use React\Filesystem\Node\File;
use Symfony\Component\Console\Style\SymfonyStyle;

class SiteGenerator
{
    private $config;
    private $filesystem;
    private $loop;
    private $io;
    private $pageResolver;
    private $contentResolver;

    public function __construct(PageResolverInterface $pageResolver, FileContentResolver $contentResolver, Config $config, FilesystemInterface $filesystem, LoopInterface $loop, SymfonyStyle $io = null)
    {
        $this->pageResolver = $pageResolver;
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->loop = $loop;
        $this->io = $io;
        $this->contentResolver = $contentResolver;
        if (null === $io) {
            $this->io = $this->getMockIo();
        }
    }

    public function build()
    {
        $this->io->title('The site creating has begun');

        $this->io->section('Removing build folder for fresh start');
        await($this->removeBuildFolder(), $this->loop);
        $this->io->success('Folder Removed!');

        $folders = [];
        $this->io->title('Creating Folders');
        await($this->createFoldersForBuildFiles($folders), $this->loop);
        $this->io->listing($folders);
        $this->io->success('Folders Created!');

        $this->io->section('Creating static pages');
        await($this->createStaticPages(), $this->loop);
        $this->io->success('Pages Created!');

        if (empty($this->config->getAssetsBaseFolder())) {
            return;
        }

        $this->io->section('Scanning pages for used assets');
        $images = [];
        await($this->lookForImages($images), $this->loop);
        if (empty($images)) {
            return;
        }

        $images = array_unique($images);
        $this->io->listing($images);

        $imageDirectories = [];

        $this->io->section('Processing assets');
        foreach ($images as $key => $image) {
            if (!(new Str($image))->startsWith('/')) {
                unset($images[$key]);
                $this->io->warning(sprintf('asset %s does not seem to start with /. This breaks the asset path. Please fix', $image));
                continue;
            }

            $imageDirectory = $this->config->getBuildBaseFolder().(new Str($image))->getBeforeLast('/');
            if ($this->config->getBuildBaseFolder() !== $imageDirectory) {
                $imageDirectories[] = $imageDirectory;
            }
        }

        $imageDirectories = array_unique($imageDirectories);

        if (0 === count($imageDirectories)) {
            $this->io->section('Skipping, no asset directories to create.');
        }

        if (!empty($imageDirectories)) {
            $this->io->section('Creating necessary folders for all assets');
            $folderParsers = new FolderParser($imageDirectories);
            foreach ($folderParsers->parse() as $folder) {
                $createDir = $this->filesystem->dir($folder)->createRecursive();
                await($createDir, $this->loop);
            }
        }

        $this->io->success('');

        $this->io->section('Copying assets to right folders');
        $this->io->listing($images);
        foreach ($images as $image) {
            $sourceImage = $this->config->getAssetsBaseFolder().$image;
            $targetImage = $this->config->getBuildBaseFolder().$image;
            $source = $this->filesystem->file($sourceImage);
            $target = $this->filesystem->file($targetImage);
            $copy = $source->copy($target);
            await($copy, $this->loop);
        }
        $this->io->success('Done! Site successfully generated!');
    }

    public function lookForImages(array &$images)
    {
        return $this->filesystem->dir($this->config->getBuildBaseFolder())
            ->lsRecursive()
            ->then(function ($nodes) use (&$images) {
                foreach ($nodes as $node) {
                    if ($node instanceof File) {
                        $imageExtractor = $node->getContents()
                            ->then(function ($content) use (&$images, &$processedFileCount) {
                                $imageExtractor = new AssetExtractor($content);

                                return $imageExtractor->getAssets();
                            });

                        $extractedImages = await($imageExtractor, $this->loop);
                        $images = array_merge($extractedImages, $images);
                    }
                }
            });
    }

    public function getMockIo()
    {
        return new class() {
            public function text(...$arg)
            {
            }

            public function title(...$arg)
            {
            }

            public function section(...$arg)
            {
            }

            public function success(...$arg)
            {
            }

            public function listing(...$arg)
            {
            }

            public function warning(...$arg)
            {
            }
        };
    }

    private function createStaticPages()
    {
        return $this->pageResolver->getPages()
            ->then(function (array $pages) {
                foreach ($pages as $filename) {
                    $htmlGenerator = $this->contentResolver->resolve($filename);
                    $promise = $htmlGenerator->getPageContent()
                ->then(function ($content) use ($filename) {
                    $builtFilename = $this->pageResolver->getBuildFilename($filename);
                    $this->io->text(sprintf('* %s', $builtFilename));

                    return $this->filesystem->file($builtFilename)->putContents($content);
                });
                    await($promise, $this->loop);
                }
            });
    }

    private function createFoldersForBuildFiles(array &$builtFolder)
    {
        $pages = $this->pageResolver->getPages();

        return $pages->then(function (array $files) use (&$builtFolder) {
            $parser = new FolderParser($files);
            foreach ($parser->parse() as $folder) {
                $folder = $this->pageResolver->getBuildFolder($folder);
                try {
                    await($this->filesystem->dir($folder)->createRecursive('rwxrwx---'), $this->loop);
                    array_push($builtFolder, $folder);
                } catch (\Exception $exception) {
//                    echo $exception->getMessage() . PHP_EOL;
                }
            }
        });
    }

    private function removeBuildFolder()
    {
        return $this->filesystem->dir($this->config->getBuildBaseFolder())
            ->stat()
            ->then(function () {
                return $this->filesystem->dir($this->config->getBuildBaseFolder())->removeRecursive();
            }, function (\Exception $exception) {
            });
    }
}
