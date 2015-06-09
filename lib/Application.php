<?php namespace MtGTutor\CLI\ImageHelper;

use MtGTutor\CLI\ImageHelper\Commands\CommandInterface;

// Constants
define('DEFAULT_SRC_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src');
define('DEFAULT_DEST_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR .  'dest');

/**
 * Main Entry point
 * @author PascalKleindienst <mail@pascalkleindienst.de>
 * @version 1.0
 */
class Application
{
    /**
     * Version
     */
    const VERSION = '1.0';

    /**
     * @var Arguments
     */
    public $args;

    /**
     * @param Arguments $args
     */
    public function __construct(Arguments $args)
    {
        $this->args = $args;
    }

    /**
     * Main Entry point
     * @param CommandInterface $command
     * @return int
     */
    public function run(CommandInterface $command = null)
    {
        // no arguments (except for imageHelper) => display help text
        if ($this->args->isEmpty()) {
            echo "usage: imageHelper [--version] [--src-dir=<path>] [--dest-dir=<path>] " . PHP_EOL
                . "                   [--keep-old] [-k] [-v] <command> [<args>]";
        }

        // display version
        if ($this->args->isFlagSet('v') || $this->args->optionEquals('version', true)) {
            echo 'imageHelper version ' . self::VERSION;
        }

        // run command
        if (!is_null($command)) {
            $command->run();
        }
        
        return 0;
    }
}
