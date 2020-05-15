<?php

namespace Tests;

use Paphper\Exceptions\ContentResolverException;
use Paphper\FileContentResolver;
use PHPUnit\Framework\TestCase;

class FileContentResolverTest extends TestCase
{
    public function testUnresolvedExtensionThrowsException()
    {
        $resolver = new FileContentResolver();
        $this->expectException(ContentResolverException::class);
        $resolver->resolve('naren.html');
    }
}
