<?php

namespace Paphper\Responses;

use Paphper\Commands\Watch;
use Paphper\Config;
use Psr\Http\Message\ServerRequestInterface;
use React\Filesystem\FilesystemInterface;
use React\Http\Response;
use React\Promise\PromiseInterface;

class Html extends AbstractResponse
{
    protected $config;
    protected $path;
    protected $filename;
    protected $fullPath;
    protected $filesystem;
    protected $pathContentMap = [];

    public function __construct(ServerRequestInterface $request, Config $config, FilesystemInterface $filesystem)
    {
        parent::__construct($request, $config, $filesystem);

        $this->headers = array_merge($this->headers, [
            'Content-Type' => 'text/html',
        ]);

        $filename = $this->config->getBuildBaseFolder().'/'.$this->request->getUri()->getPath().'/index.html';
        $this->filename = $this->removeMultipleSlashes($filename);
        $this->filesystem = $filesystem;
    }

    public function toResponse(): PromiseInterface
    {
        $file = $this->filesystem->file($this->filename);
        $script = $this->getWatchJs((string) $this->request->getUri()->__toString());

        return
            $file->exists()
                ->then(function () use ($file, $script) {
                    return $file->getContents()
                        ->then(function ($content) use ($script) {
                            $content .= $script;
                            $hash = $this->getContentHash($content.$this->path);
                            if (in_array('Watch', $this->request->getHeader('X-Intent')) && isset(Watch::$contentHashMap[$this->path]) && Watch::$contentHashMap[$this->path] === $hash) {
                                return new Response(204, $this->headers);
                            }
                            Watch::$contentHashMap[$this->path] = $hash;

                            return new Response(200, $this->headers, $content);
                        }, $this->responseNotFound());
                });
    }

    protected function getWatchJs(string $path): string
    {
        $js = <<<HTML
<script>
function watch() {
    fetch('${path}',{
        mode: 'cors',
        headers :{
            'Content-Type' : 'text/plain',
            'X-Intent' : 'Watch',
        },
        cache : 'no-cache',
    })
        .then(function (response) {
            return response.text();
        }).then(function (html) {
            if(html){
                const page = document.getElementsByTagName('html')[0]
                page.innerHTML = html;
            }

    })
}
setInterval(watch, 2000);
</script>
HTML;

        return $js;
    }

    private function getContentHash(string $content): string
    {
        return hash('crc32', $content);
    }
}
