<?php

namespace Paphper;

class Config
{
    private $options = [
        'pages_dir' => '',
        'layout_dir' => '',
        'build_dir' => '',
        'assets_dir' => '',
        'is_dev' => false,
        'port' => '8888',
        'cache_dir' => '',
    ];

    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    public function getLayoutBaseFolder()
    {
        return $this->options['layout_dir'];
    }

    public function getPageBaseFolder()
    {
        return $this->options['pages_dir'];
    }

    public function getBuildBaseFolder()
    {
        return $this->options['build_dir'];
    }

    public function getAssetsBaseFolder()
    {
        return $this->options['assets_dir'];
    }

    public function getPort()
    {
        return $this->options['port'];
    }

    public function getCacheDir()
    {
        return $this->options['cache_dir'];
    }
}
