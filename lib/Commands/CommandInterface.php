<?php namespace MtGTutor\CLI\ImageHelper\Commands;

use MtGTutor\CLI\ImageHelper\Arguments;

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
    public function __construct(Arguments $args);

    /**
     * Run the command
     * @return void
     */
    public function run();
}
