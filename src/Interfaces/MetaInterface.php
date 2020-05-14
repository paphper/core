<?php

namespace Paphper\Interfaces;

interface MetaInterface
{
    public function getBody(): ?string;

    public function getLayout(): string;

    public function get(string $key): ?string;

    public function getExtraMetas(): array;
}
