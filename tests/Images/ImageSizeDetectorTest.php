<?php

namespace Tests\Images;

use Paphper\Images\ImageSizeDetector;
use PHPUnit\Framework\TestCase;

class ImageSizeDetectorTest extends TestCase
{
    public function testImageSize()
    {
        $detector = new ImageSizeDetector([
            'naren/naren.jpg',
            'naren/naren_25x25.jpg',
            'naren/naren_50x50.jpg',
            'films/test_16x9.jpg',
            'naren.jpg',
        ]);

        $result = [
            'naren/naren.jpg' => [
                '25x25',
                '50x50',
            ],
            'films/test.jpg' => [
                '16x9',
            ],
            'naren.jpg' => [],
        ];

        $this->assertSame($result, $detector->getSizes());
    }
}
