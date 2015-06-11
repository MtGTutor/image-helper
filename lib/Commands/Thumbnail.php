<?php namespace MtGTutor\CLI\ImageHelper\Commands;

use MtGTutor\CLI\ImageHelper\Application;
use MtGTutor\CLI\ImageHelper\Arguments;
use MtGTutor\CLI\ImageHelper\Container;
use MtGTutor\CLI\ImageHelper\FileHandler;

/**
 * Commands Class to create thumbnaisls
 * @author PascalKleindienst <mail@pascalkleindienst.de>
 * @version 1.0
 */
class Thumbnail implements CommandInterface
{
    /**
     * @var Arguments
     */
    protected $args;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var FileHandler
     */
    protected $fileHandler;

     /**
     * Set Arguments, Filehandler and Container
     * @param Arguments $args
     * @param FileHandler $filehandler
     * @param Container $container
     */
    public function __construct(Arguments $args, FileHandler $filehandler, Container $container)
    {
        // setter
        $this->args        = $args;
        $this->container   = $container;
        $this->fileHandler = $filehandler;

        // no custom size height -> set height to half of normal height
        if (!array_key_exists('height', $args['options']) && $this->fileHandler->height !== null) {
            $this->fileHandler->height = ceil($this->fileHandler->height / 2);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function run()
    {
        $folders = $this->args['arguments'];

        // if no folders are specified - display available list from --src-dir to select from
        if (empty($folders)) {
            $folders = $this->fileHandler->selectFromList();
        }

        // minify
        echo "\n\nStart Creating Thumbnails: \n";
        $this->createThumbnails($folders);
    }

     /**
     * Create Thumbnails
     * @param  array $folders
     * @return void
     */
    protected function createThumbnails($folders)
    {
        // Debug Info
        $maxFiles = 0;
        $closed   = 0;

        // get max number of files
        $this->fileHandler->files($folders, function ($files) use (&$maxFiles) {
            $maxFiles += count($files);
        });

        // minify files
        $this->fileHandler->files(
            $folders,
            function ($files, $folder) use (&$closed, &$maxFiles) {
                foreach ($files as $file) {
                    // save path
                    $save = $this->fileHandler->getNewFilename($folder, $file, function ($folder, $file) {
                        $pathinfo = pathinfo($file);
                        return str_replace($pathinfo['filename'], $pathinfo['filename'] . '.thumb', $file);
                    });

                    // echo user info
                    if (Application::$flags['debug']) {
                        echo str_pad("Saving: " . basename($save), 52, " ", STR_PAD_RIGHT);
                    }
                    progress(++$closed, $maxFiles, !Application::$flags['debug']);

                    // resize image
                    $this->fileHandler->resize($file, $save);
                }
            }
        );
    }
}
