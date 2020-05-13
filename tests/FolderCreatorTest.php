<?php

namespace Tests;

use function Clue\React\Block\await;
use Paphper\Config;
use Paphper\FolderCreator;

class FolderCreatorTest extends AbstractTestCase
{
    public function tearDown(): void
    {
        parent::tearDown();
        $configData = include getBaseDir().'/config.php';
        $config = new Config($configData);
        $promise = $this->filesystem->dir($config->getBuildBaseFolder())
            ->stat()
            ->then(function () use ($config) {
                return $this->filesystem->dir($config->getBuildBaseFolder())->removeRecursive();
            }, function (\Exception $exception) {
            });
        await($promise, $this->loop);
    }

    public function testFolderCreationWorksCorrectly()
    {
        $configData = include getBaseDir().'/config.php';
        $config = new Config($configData);
        $folderCreator = new FolderCreator($this->filesystem, $config);
        $folders = await($folderCreator->getFoldersToCreate(), $this->loop);

        //check if the folder exists,
        //if tried to delete directly it just hangs if the directory is not there.
        $promise = $this->filesystem->dir($config->getBuildBaseFolder())
            ->stat()
            ->then(function () use ($config) {
                return $this->filesystem->dir($config->getBuildBaseFolder())->removeRecursive();
            }, function (\Exception $exception) {
            });
        await($promise, $this->loop);

        $this->assertContains($config->getBuildBaseFolder().'/blogs/2020', $folders);
        $this->assertContains($config->getBuildBaseFolder().'/bios/salam', $folders);
        $this->assertContains($config->getBuildBaseFolder().'/bios/naren', $folders);
        $this->assertContains($config->getBuildBaseFolder().'/non-html', $folders);

        foreach ($folders as $folder) {
            try {
                await($this->filesystem->dir($folder)->createRecursive('rwxrwx---'), $this->loop);
            } catch (\Exception $exception) {
//                echo $exception->getMessage() . ' ' . $exception->getFile();
            }
        }

        foreach ($folders as $folder) {
            $this->assertTrue(is_dir($folder));
        }
    }
}
