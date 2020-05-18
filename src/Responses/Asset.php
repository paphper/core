<?php

namespace Paphper\Responses;

use Intervention\Image\ImageManager;
use Paphper\Config;
use Paphper\Images\ImageNameResolver;
use Paphper\Images\ImageSizeDetector;
use Paphper\Utils\Str;
use Psr\Http\Message\ServerRequestInterface;
use React\Filesystem\FilesystemInterface;
use React\Http\Response;
use React\Promise\PromiseInterface;

class Asset extends AbstractResponse
{
    protected $fileExtension;
    private $imageManager;

    public function __construct(ServerRequestInterface $request, Config $config, FilesystemInterface $filesystem, ImageManager $imageManager)
    {
        parent::__construct($request, $config, $filesystem, );
        $path = $this->request->getUri()->getPath();
        $filename = $this->config->getBuildBaseFolder().'/'.$path;
        $this->filename = $this->removeMultipleSlashes($filename);
        $this->filesystem = $filesystem;
        $this->imageManager = $imageManager;
        $this->fileExtension = (new Str($path))->getAfterLast('.');
    }

    public function toResponse(): PromiseInterface
    {
        $file = $this->filesystem->file($this->filename);

        return $file->exists()
            ->then(function () use ($file) {
                return $file->getContents()
                    ->then(function ($content) {
                        return new Response(200, array_merge($this->headers, $this->getMimeTypeHeader($this->fileExtension)), $content);
                    });
            }, function () {
                $assetsFolder = $this->config->getAssetsBaseFolder();
                $assetsImage = $this->removeMultipleSlashes($assetsFolder.'/'.$this->path);

                $detector = new ImageSizeDetector([$assetsImage]);
                $assetsImage = $detector->getOriginals()[0];
                $imageFile = $this->filesystem->file($assetsImage);

                return $imageFile->exists()
                    ->then(function () use ($imageFile, $detector, $assetsImage) {
                        $buildImagePath = $this->removeMultipleSlashes($this->config->getBuildBaseFolder().'/'.$this->path);
                        $buildImageFolder = (new Str($buildImagePath))->getBeforeLast('/');
                        $directory = $this->filesystem->dir($buildImageFolder);

                        $sizes = $detector->getSizes();

                        if (!empty($sizes[$assetsImage])) {
                            foreach ($sizes[$assetsImage] as $size) {
                                $imageFile->getContents()->then(function ($content) use ($assetsImage, $size) {
                                    $finalImage = str_replace($this->config->getAssetsBaseFolder(), $this->config->getBuildBaseFolder(), $assetsImage);
                                    $resizeFile = (new ImageNameResolver($finalImage, $size))->getFilename();

                                    [$width, $height] = explode('x', $size);
                                    $data = $this->imageManager->make($content)->resize($width, $height)->encode();

                                    return $this->filesystem->file($resizeFile)->putContents($data);
                                });
                            }
                        }

                        $directory->stat()
                            ->then(function () {
                                return '';
                            }, function () use ($directory) {
                                return $directory->createRecursive();
                            })->then(function () use ($imageFile, $buildImagePath) {
                                $imageFile->copy($this->filesystem->file($buildImagePath));
                            });

                        return $imageFile->getContents()
                            ->then(function ($content) {
                                return new Response(200, array_merge($this->headers, $this->getMimeTypeHeader($this->fileExtension)), $content);
                            });
                    }, $this->responseNotFound());
            });
    }
}
