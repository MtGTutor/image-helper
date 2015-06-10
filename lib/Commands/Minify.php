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
class Minify implements CommandInterface
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
        // setter
        $this->args        = $args;
        $this->container   = $container;
        $this->fileHandler = $filehandler;

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
        $folders = $this->args['arguments'];

        // if no folders are specified - display available list from --src-dir to select from
        if (empty($folders)) {
            $folders = $this->fileHandler->selectFromList();
        }

        // minify
        echo "\n\nStart Optimizing: \n";
        $this->runMinifier($folders);
    }

    /**
     * Minify images
     * @param  array $folders
     * @return void
     */
    protected function runMinifier($folders)
    {
        // optimizer and image manager
        $optimizer = $this->container->resolve(
            'Optimizer',
            $this->getOptimizerPath('optipng'),
            $this->getOptimizerPath('jpegoptim'),
            $this->getOptimizerPath('gifsicle')
        );
        $driver       = (!extension_loaded('imagick')) ? 'gd' : 'imagick';
        $imageManager = $this->container->resolve('ImageManager', $driver);
        $maxFiles     = 0;
        $closed       = 0;

        // get max number of files
        $this->fileHandler->files($folders, function ($files) use (&$maxFiles) {
            $maxFiles += count($files);
        });

        // minify files
        $this->fileHandler->files(
            $folders,
            function ($files, $folder) use (&$closed, &$maxFiles, $imageManager, $optimizer) {
                foreach ($files as $file) {
                    // save path
                    $save = $this->fileHandler->getNewFilename($folder, $file);
                    
                    // echo user info
                    if (Application::$flags['debug']) {
                        echo str_pad("Optimizing: " . basename($save), 52, " ", STR_PAD_RIGHT);
                    }
                    progress(++$closed, $maxFiles, !Application::$flags['debug']);

                    // resize image
                    $this->fileHandler->resize($imageManager, $file, $save);

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
