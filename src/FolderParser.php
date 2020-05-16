<?php

namespace Paphper;

use Paphper\Utils\Str;

class FolderParser
{
    private $folders;
    private $replaces = [
        '/index.html',
        '/index.md',
        '.md',
        '.html',
        '.blade.php',
        '/index.blade.php',
    ];
    private $sortedArr = [];

    public function __construct(array $folders)
    {
        $this->folders = $folders;
        $this->parseFolders();
    }

    public function parse(): array
    {
        return array_unique($this->folders);
    }

    private function parseFolders()
    {
        if (empty($this->folders)) {
            $this->sortedArr = [];

            return;
        }

        usort($this->folders, function (string $a, string $b) {
            return substr_count($a, '/') > substr_count($b, '/');
        });
        $this->folders = array_map(function ($folder) {
            $string = new Str($folder);
            foreach ($this->replaces as $replace) {
                if ($string->endsWith($replace)) {
                    return $string->removeLast($replace);
                }
            }

            return (string) $string;
        }, $this->folders);

        foreach ($this->folders as $key => $folder) {
            foreach ($this->folders as $childKey => $childFolder) {
                $str = new Str($childFolder);
                if ($str->startsWith($folder) && $folder !== $childFolder) {
                    unset($this->folders[$key]);
                }
            }
        }
    }
}
