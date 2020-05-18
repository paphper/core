<?php

namespace Tests\Images;

use Paphper\Images\ImageNameResolver;
use PHPUnit\Framework\TestCase;

class ImageNameResolverTest extends TestCase
{
    public function testImageNameResolver()
    {
        $imageNameResolver = new ImageNameResolver('naren.jpg', '25x25');
        $this->assertSame('naren_25x25.jpg', $imageNameResolver->getFilename());
    }

    public function testWorksWithFolderName()
    {
        $imageNameResolver = new ImageNameResolver('path/to/folder_naren.jpg', '25x25');
        $this->assertSame('path/to/folder_naren_25x25.jpg', $imageNameResolver->getFilename());
    }
}
