<?php namespace MtGTutor\CLI\ImageHelper\Commands;

use MtGTutor\CLI\ImageHelper\Application;
use MtGTutor\CLI\ImageHelper\Arguments;
use MtGTutor\CLI\ImageHelper\Container;
use MtGTutor\CLI\ImageHelper\FileHandler;

/**
 * Abstract Command Class
 * @author PascalKleindienst <mail@pascalkleindienst.de>
 * @version 1.0
 */
abstract class AbstractCommand implements CommandInterface
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
     * selected folders
     * @var array
     */
    protected $folders = [];

    /**
     * @var array
     */
    protected $debugInfo = [
        'maxFiles' => 0,
        'closed'   => 0
    ];

    /**
     * Dependency injection
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
    }

    /**
     * Run the command
     * @return void
     */
    public function run()
    {
        $this->folders = $this->args['arguments'];

        // if no folders are specified - display available list from --src-dir to select from
        if (empty($this->folders)) {
            $this->folders = $this->fileHandler->selectFromList();
        }

        // get max number of files
        $this->fileHandler->files($this->folders, function ($files) {
            $this->debugInfo['maxFiles'] += count($files);
        });
    }

    /**
     * prints debug info
     * @param  string $message
     * @return void
     */
    protected function printDebugInfo($message)
    {
        // echo debug info
        if (Application::$flags['debug']) {
            echo str_pad($message, 52, " ", STR_PAD_RIGHT);
        }
        progress(++$this->debugInfo['closed'], $this->debugInfo['maxFiles'], !Application::$flags['debug']);
    }
}
