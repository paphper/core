<?php

namespace Paphper\Interfaces;

use React\Promise\PromiseInterface;

interface MetaInterface
{
    public function getBody(): ?string;

    public function getLayout(): string;

    public function getLayoutContent(): PromiseInterface;

    public function get(string $key): ?string;

    public function getExtraMetas(): array;

    /**
     * Since we are working with promises for filesystem,
     * the file has to be read once to process content of the page.
     * If we process promises individually in every call it becomes hard to return as types and becomes a callback hell.
     * Hence we call the process function to set all the properties.
     *
     * @return PromiseInterface
     */
    public function process(): PromiseInterface;
}
