<?php namespace MtGTutor\CLI\ImageHelper\Commands;

use MtGTutor\CLI\ImageHelper\Arguments;
use MtGTutor\CLI\ImageHelper\Container;

/**
 * Command Interface used by all Commands
 * @author PascalKleindienst <mail@pascalkleindienst.de>
 * @version 1.0
 */
interface CommandInterface
{
    /**
     * Set Arguments
     * @param Arguments $args
     */
    public function __construct(Arguments $args, Container $container);

    /**
     * Run the command
     * @return void
     */
    public function run();
}
