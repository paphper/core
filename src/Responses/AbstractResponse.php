<?php

namespace Paphper\Responses;

use Paphper\Config;
use Psr\Http\Message\ServerRequestInterface;
use React\Filesystem\FilesystemInterface;
use React\Http\Response;
use React\Promise\PromiseInterface;

abstract class AbstractResponse
{
    protected $config;
    protected $path;
    protected $filename;
    protected $fullPath;
    protected $filesystem;
    protected $pathContentMap = [];
    protected $request;

    protected $headers = [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => '*',
    ];

    public function __construct(ServerRequestInterface $request, Config $config, FilesystemInterface $filesystem)
    {
        $this->config = $config;
        $this->path = $request->getUri()->getPath();
        $this->request = $request;
    }

    abstract public function toResponse(): PromiseInterface;

    public function responseNotFound()
    {
        return function () {
            return new Response(404, $this->headers, 'Not Found!');
        };
    }

    protected function removeMultipleSlashes(string $string)
    {
        $string = str_replace('///', '/', $string);

        return str_replace('//', '/', $string);
    }

    final protected function getMimeTypeHeader(string $imageType): array
    {
        $imageMimeTypes = [
            'png' => ['Content-Type' => 'image/png'],
            'jpeg' => ['Content-Type' => 'image/jpeg'],
            'jpg' => ['Content-Type' => 'image/jpg'],
            'ico' => ['Content-Type' => 'image/x-icon'],
            'css' => ['Content-Type' => 'text/css'],
            'js' => ['Content-Type' => 'text/javascript'],
        ];

        return $imageMimeTypes[$imageType];
    }
}
