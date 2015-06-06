<?php namespace MtGTutor\CLI\ImageHelper\Commands;

use MtGTutor\CLI\ImageHelper\Arguments;

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
     * @var string
     */
    private $usedOS = self::LINUX;

    /**
     * @var string
     */
    private $architecture = self::BIT32;

    /**
     * Set Arguments
     * @param Arguments $args
     */
    public function __construct(Arguments $args)
    {
        // set args
        $this->args = $args;

        var_dump($this->usedOS, $this->architecture);
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
        $srcDir  = $this->args->isFlagSet('src-dir') ? $this->args['flags']['src-dir'] : DEFAULT_SRC_DIR;
        $destDir = $this->args->isFlagSet('dest-dir') ? $this->args['flags']['dest-dir'] : DEFAULT_DEST_DIR;
         
        // if no folders are specified - display available list from --src-dir to select from
        if (empty($folders)) {
            $folders = $this->selectFromList($srcDir);
        }
        
        //@TODO: minify
        // $optimizer = new ImageOptimizer([
        //     ImageOptimizer::OPTIMIZER_OPTIPNG   => getOptimizerPath('optipng'),
        //     ImageOptimizer::OPTIMIZER_JPEGOPTIM => getOptimizerPath('jpegoptim'),
        //     ImageOptimizer::OPTIMIZER_GIFSICLE  => getOptimizerPath('gifsicle')
        // ]);

        // $optimizer->optimize(dirname(__FILE__) . "/test.jpg"); //return true
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
        $path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'optimizers' . DIRECTORY_SEPARATOR;
        
        // check if windows, so we can append .exe
        if ($this->usedOS === self::WINDOWS) {
            $optimizer .= '.exe';
        }

        // return path
        return $path . $this->usedOS. DIRECTORY_SEPARATOR . $this->architecture . DIRECTORY_SEPARATOR . $optimizer;
    }
}
