<?php namespace MtGTutor\CLI\ImageHelper\Commands;

use MtGTutor\CLI\ImageHelper\Arguments;
use MtGTutor\CLI\ImageHelper\Container;
use Intervention\Image\ImageManager;

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
     * @var int|null
     */
    const HEIGHT = 510;

    /**
     * @var int|null
     */
    const WIDTH = null;

    /**
     * @var Arguments
     */
    protected $args;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var boolean
     */
    protected $isDebug = false;

    /**
     * @var string
     */
    private $usedOS = self::LINUX;

    /**
     * @var string
     */
    private $architecture = self::BIT32;

    /**
     * Set Arguments and Container
     * @param Arguments $args
     * @param Container $container
     */
    public function __construct(Arguments $args, Container $container)
    {
        // setter
        $this->args      = $args;
        $this->container = $container;
        $this->isDebug   = $this->args->isFlagSet('d') || $this->args->optionEquals('debug', true);

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
     * @todo : add options for width and height
     */
    public function run()
    {
        $folders = $this->args['arguments'];
        $srcDir  = $this->args->isFlagSet('src-dir') ? $this->args['flags']['src-dir'] : DEFAULT_SRC_DIR;
        $destDir = $this->args->isFlagSet('dest-dir') ? $this->args['flags']['dest-dir'] : DEFAULT_DEST_DIR;

        // if no folders are specified - display available list from --src-dir to select from
        if (empty($folders)) {
            $folders = $this->selectFromList($srcDir);
        }
        
        // minify
        echo "\n\nStart Optimizing: \n";
        $this->runMinifier($folders, $srcDir, $destDir);
    }

    /**
     * Minify images
     * @param  array $folders
     * @param  string $src
     * @param  string $dest
     * @return void
     */
    protected function runMinifier($folders, $src, $dest)
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
        getFiles($folders, $src, function ($files) use (&$maxFiles) {
            $maxFiles += count($files);
        });

        // minify files
        getFiles(
            $folders,
            $src,
            function ($files, $folder, $src) use (&$closed, &$maxFiles, $dest, $imageManager, $optimizer) {
                foreach ($files as $file) {
                    // save path
                    $save = $file;
                    if ($this->args->isFlagSet('k') || $this->args->optionEquals('keep', true)) {
                        $save = $this->getFilename($src, $dest, $folder, $save);
                        $dir  = $dest . DIRECTORY_SEPARATOR . $folder;

                        if (!file_exists($dir)) {
                            mkdir($dir);
                        }
                    }

                    // echo user info
                    if ($this->isDebug) {
                        echo str_pad("Optimizing: " . basename($save), 52, " ", STR_PAD_RIGHT);
                    }
                    progress(++$closed, $maxFiles, !$this->isDebug);

                    // resize image
                    $image = $imageManager->make($file)->resize(self::WIDTH, self::HEIGHT, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $image->save($save);
                    $image->destroy(); #free memory

                    // optimize
                    $optimizer->optimize($save);
                }
            }
        );
    }

    /**
     * Get new filename for saving
     * @param  string $src
     * @param  string $dest
     * @param  string $folder
     * @param  string $file
     * @return string
     */
    public function getFilename($src, $dest, $folder, $file)
    {
        $basename = basename($file);
        $baseNew  = str_replace([' ', '.full.'], ['+', '.'], $basename);

        // Replace src with dest, Make folder lowercase, and make filename web friendly
        return str_replace(
            [ $src, $folder, $basename ],
            [ $dest, strtolower($folder), $baseNew ],
            $file
        );
    }

    /**
     * Get folders from user selection
     * @return array
     */
    protected function selectFromList($dir)
    {
        $folders = [];
        $list = glob($dir . '/*', GLOB_ONLYDIR);

        foreach ($list as $key => $dir) {
            $list[$key] = basename($dir);
        }

        // Output function
        $output = function ($list) {
            $max = count($list);
            $list = array_values($list);

            echo 'Available Sets: ' . PHP_EOL;
            for ($i=0; $i < $max; $i++) {
                echo '[' . ($i+1) . ']: ' . $list[$i] . PHP_EOL;
            }
        };

        // get selection
        while (true) {
            // get user input
            $output($list);
            echo 'Select a set by number (Enter nothing to end selection): ';
            $line = fgets(STDIN);
            $line = trim($line);

            // process selection
            if (is_numeric($line) && array_key_exists($line-1, $list)) {
                // add selection to folders
                $key = $line - 1;
                $folders[] = $list[$key];

                // remove selection form list
                unset($list[$key]);
            }

            // abort
            if ($line === "" || empty($list)) {
                break;
            }
        }

        echo 'Selected sets: ' . implode(', ', $folders);

        return $folders;
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
