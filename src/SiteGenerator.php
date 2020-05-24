<?php

namespace Paphper;

use function Clue\React\Block\await;
use Intervention\Image\ImageManager;
use Paphper\Contracts\PageResolverInterface;
use Paphper\Extractors\AssetExtractor;
use Paphper\Images\ImageNameResolver;
use Paphper\Images\ImageSizeDetector;
use Paphper\Utils\Str;
use React\EventLoop\LoopInterface;
use React\Filesystem\FilesystemInterface;
use React\Filesystem\Node\File;

class SiteGenerator
{
    private $config;
    private $filesystem;
    private $loop;
    private $io;
    private $pageResolver;
    private $contentResolver;
    private $imageManager;
    private $input;
    private $output;

    public function __construct(PageResolverInterface $pageResolver, FileContentResolver $contentResolver, Config $config, FilesystemInterface $filesystem, LoopInterface $loop, ImageManager $imageManager, $io = null)
    {
        $this->pageResolver = $pageResolver;
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->loop = $loop;
        $this->io = $io;
        if (null === $io) {
            $this->io = $this->getMockIo();
        }

        $this->contentResolver = $contentResolver;
        $this->imageManager = $imageManager;
    }

    public function build()
    {
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

        $images = [];
        await($this->lookForImages($images), $this->loop);
        if (empty($images)) {
            return;
        }
        $this->io->listing($images);

        $imageDirectories = [];

        $this->io->section('Scanning pages for used assets');

        $imageSizeParser = new ImageSizeDetector($images);

        $this->io->listing($imageSizeParser->getOriginals());

        foreach ($imageSizeParser->getOriginals() as $key => $image) {
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
        $this->io->listing($imageSizeParser->getOriginals());
        $this->processImages($imageSizeParser);
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
            public function __call($name, $args)
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

    /**
     * @param array $images
     *
     * @throws \Exception
     */
    private function processImages(ImageSizeDetector $images): void
    {
        $sizes = $images->getSizes();
        foreach ($images->getOriginals() as $image) {
            if (!(new Str($image))->startsWith('/')) {
                continue;
            }
            $sourceImage = $this->config->getAssetsBaseFolder().$image;
            $targetImage = $this->config->getBuildBaseFolder().$image;
            $source = $this->filesystem->file($sourceImage);

            $promise = $source->exists()->then(function () use ($targetImage, $source, $image, $sourceImage, $sizes) {
                $target = $this->filesystem->file($targetImage);
                $copy = $source->copy($target);
                await($copy, $this->loop);
                if (!empty($sizes[$image])) {
                    $this->io->section('Resizing '.$image);
                    $this->io->listing($sizes[$image]);
                    foreach ($sizes[$image] as $size) {
                        $promise = $this->filesystem->file($sourceImage)->getContents()->then(function ($content) use ($image, $size) {
                            $resizeFile = $this->config->getBuildBaseFolder().(new ImageNameResolver($image, $size))->getFilename();
                            [$width, $height] = explode('x', $size);
                            $data = $this->imageManager->make($content)->resize($width, $height)->encode();

                            return $this->filesystem->file($resizeFile)->putContents($data);
                        });
                        await($promise, $this->loop);
                    }
                }
            }, function () use ($image) {
                $this->io->warning(sprintf('Asset %s missing but referenced in the html. Skipping', $image));
            });

            await($promise, $this->loop);
        }
    }
}
