<?php

namespace Paphper\Contracts;

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
