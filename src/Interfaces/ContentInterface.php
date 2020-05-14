<?php

namespace Paphper\Interfaces;

use React\Promise\PromiseInterface;

interface ContentInterface
{
    /**
     *  Returns html content for a file.
     *
     * @return PromiseInterface<string>
     */
    public function getPageContent(): PromiseInterface;
}
