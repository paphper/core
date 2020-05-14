<?php

namespace Paphper\Contents;

use Paphper\Interfaces\MetaInterface;

class MetaParser implements MetaInterface
{
    private $finalCollection = [];
    private $body;

    public function __construct(string $content, string $body)
    {
        $this->body = $body;
        $attributes = explode(PHP_EOL, $content);
        foreach (array_filter($attributes) as $attr) {
            [$key, $value] = explode(':', $attr);
            $this->finalCollection[trim($key)] = trim($value);
        }
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function getLayout(): string
    {
        return $this->get('layout');
    }

    public function get(string $key): ?string
    {
        return $this->finalCollection[$key] ?? null;
    }

    public function getExtraMetas(): array
    {
        $extraMetas = $this->finalCollection;
        unset($extraMetas['layout']);

        return  $this->finalCollection;
    }
}
