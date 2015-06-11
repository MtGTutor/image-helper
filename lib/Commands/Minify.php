<?php namespace MtGTutor\CLI\ImageHelper\Commands;

use MtGTutor\CLI\ImageHelper\Application;
use MtGTutor\CLI\ImageHelper\Arguments;
use MtGTutor\CLI\ImageHelper\Container;
use MtGTutor\CLI\ImageHelper\FileHandler;

/**
 * Commands Class to handle Minify Command
 * @author PascalKleindienst <mail@pascalkleindienst.de>
 * @version 1.0
 */
class Minify extends AbstractCommand implements CommandInterface
{
    /**
     * @var string
     */
    const WINDOWS = 'win';

    /**
     * @var string
     */
    const LINUX = 'linux';

    /**
     * @var string
     */
    const BIT64 = 'x64';

    /**
     * @var string
     */
    const BIT32 = 'x86';

    /**
     * @var string
     */
    private $usedOS = self::LINUX;

    /**
     * @var string
     */
    private $architecture = self::BIT32;

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

        // check for os
        if (strpos(strtolower(php_uname('s')), 'windows') !== false) {
            $this->usedOS = self::WINDOWS;
        }

        // check for 64 bit
        if (PHP_INT_SIZE === 8) {
            $this->architecture = self::BIT64;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function run()
    {
        // run parent
        parent::run();

        // minify
        echo "\n\nStart Optimizing: \n";
        $this->fileHandler->files(
            $this->folders,
            function ($files, $folder) {
                // optimizer and image manager
                $optimizer = $this->container->resolve(
                    'Optimizer',
                    $this->getOptimizerPath('optipng'),
                    $this->getOptimizerPath('jpegoptim'),
                    $this->getOptimizerPath('gifsicle')
                );

                foreach ($files as $file) {
                    // save path
                    $save = $file;
                    if (Application::$flags['keep']) {
                        $save = $this->fileHandler->getNewFilename($folder, $save);
                    }
                    
                    // echo debug info
                    $this->printDebugInfo("Optimizing: " . basename($save));

                    // resize image
                    $this->fileHandler->resize($file, $save);

                    // optimize
                    $optimizer->optimize($save);
                }
            }
        );
    }

    /**
     * Get Path to optimizer binary
     * @param  string $optimizer which optimizer
     * @return string
     */
    protected function getOptimizerPath($optimizer)
    {
        $separator = DIRECTORY_SEPARATOR;
        $path      = dirname(__FILE__) . $separator . '..' . $separator . '..' . $separator . 'optimizers' . $separator;
        
        // check if windows, so we can append .exe
        if ($this->usedOS === self::WINDOWS) {
            $optimizer .= '.exe';
        }

        // return path
        return $path . $this->usedOS. $separator . $this->architecture . $separator . $optimizer;
    }
}
