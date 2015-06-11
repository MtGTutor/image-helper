<?php namespace MtGTutor\CLI\ImageHelper\Commands;

use MtGTutor\CLI\ImageHelper\Arguments;
use MtGTutor\CLI\ImageHelper\Container;
use MtGTutor\CLI\ImageHelper\FileHandler;

/**
 * Command Interface used by all Commands
 * @author PascalKleindienst <mail@pascalkleindienst.de>
 * @version 1.0
 */
interface CommandInterface
{
    /**
     * Dependency injection
     * @param Arguments $args
     * @param FileHandler $filehandler
     * @param Container $container
     */
    public function __construct(Arguments $args, FileHandler $filehandler, Container $container);

    /**
     * Run the command
     * @return void
     */
    public function run();
}
