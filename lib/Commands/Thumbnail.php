<?php namespace MtGTutor\CLI\ImageHelper\Commands;

use MtGTutor\CLI\ImageHelper\Arguments;
use MtGTutor\CLI\ImageHelper\Container;
use MtGTutor\CLI\ImageHelper\FileHandler;

/**
 * Commands Class to create thumbnaisls
 * @author PascalKleindienst <mail@pascalkleindienst.de>
 * @version 1.0
 */
class Thumbnail extends AbstractCommand implements CommandInterface
{
     /**
     * Set Arguments, Filehandler and Container
     * @param Arguments $args
     * @param FileHandler $filehandler
     * @param Container $container
     */
    public function __construct(Arguments $args, FileHandler $filehandler, Container $container)
    {
        // dependencies
        parent::__construct($args, $filehandler, $container);

        // no custom size height -> set height to half of normal height
        if (!array_key_exists('height', $args['options']) && $this->fileHandler->height !== null) {
            $this->fileHandler->height = (int) ceil($this->fileHandler->height / 2);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function run()
    {
        // run parent
        parent::run();

        // resize
        echo "\n\nStart Creating Thumbnails: \n";
        $this->fileHandler->files(
            $this->folders,
            function ($files, $folder) {
                foreach ($files as $file) {
                    // save path
                    $save = $this->fileHandler->getNewFilename($folder, $file, function ($folder, $file) {
                        $pathinfo = pathinfo($file);
                        return str_replace($pathinfo['filename'], $pathinfo['filename'] . '.thumb', $file);
                    });

                    // echo debug info
                    $this->printDebugInfo("Saving: " . basename($save));

                    // resize image
                    $this->fileHandler->resize($file, $save);
                }
            }
        );
    }
}
