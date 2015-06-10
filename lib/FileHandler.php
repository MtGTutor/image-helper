<?php namespace MtGTutor\CLI\ImageHelper;

use Intervention\Image\ImageManager;

/**
 * File Handler
 * @author PascalKleindienst <mail@pascalkleindienst.de>
 * @version 1.0
 */
class FileHandler
{
    /**
     * @var int|null
     */
    public $height = 510;

    /**
     * @var int|null
     */
    public $width = null;

    /**
     * @var string
     */
    public $src = DEFAULT_SRC_DIR;

    /**
     * @var string
     */
    public $dest = DEFAULT_DEST_DIR;

    /**
     * @var Arguments
     */
    protected $args;

    /**
     * @var ImageManager
     */
    protected $manager;

    /**
     * Setter
     * @param Arguments $args
     */
    public function __construct(Arguments $args, ImageManager $manager)
    {
        $this->args    = $args;
        $this->manager = $manager;

        // set dirs
        if ($args->isFlagSet('src-dir')) {
            $this->src = $args['flags']['src-dir'] ;
        }
        if ($args->isFlagSet('dest-dir')) {
            $this->dest = $args['flags']['dest-dir'];
        }

        // set dimensions
        if (array_key_exists('width', $args['options'])) {
            $this->width = $args['options']['width'];
        }
        if (array_key_exists('height', $args['options'])) {
            $this->height = $args['options']['height'];
        }
    }

    /**
     * Resize image and save it
     * @param  string $file
     * @param  string $save
     */
    public function resize($file, $save)
    {
        $image = $this->manager->make($file)->resize($this->width, $this->height, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $image->save($save);
        $image->destroy(); #free memory
    }

    /**
     * Create folder for set if needed
     * @param  string $set
     */
    public function createFolder($set)
    {
        $dir = $this->dest . DIRECTORY_SEPARATOR . strtolower($set);

        if (!file_exists($dir)) {
            mkdir($dir);
        }
    }

    /**
     * get files from src
     * @param  array $folders
     * @param  \Closure $callback
     * @return array
     */
    public function files($folders, $callback = null)
    {
        $files = [];
        foreach ($folders as $folder) {
            $path    = $this->src . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR;
            $glob    = glob($path . '*.{jpg,jpeg,gif,png}', GLOB_NOSORT|GLOB_BRACE);
            $files[] = $glob;

            if (is_callable($callback)) {
                call_user_func($callback, $glob, $folder);
            }
        }

        return $files;
    }

    /**
     * Get new filename for saving
     * @param  string $folder
     * @param  string $file
     * @param \Closure $callback 
     * @return string
     */
    public function getNewFilename($folder, $file, $callback = null)
    {
        $this->createFolder($folder);
        $basename = basename($file);
        $baseNew  = str_replace([' ', '.full.'], ['+', '.'], $basename);

        // Replace src with dest, Make folder lowercase, and make filename web friendly
        $file = str_replace(
            [ $this->src, $folder, $basename ],
            [ $this->dest, strtolower($folder), $baseNew ],
            $file
        );

        // custom replacements
        if (is_callable($callback)) {
            $file = call_user_func($callback, $folder, $file);
        }
        
        return $file;
    }

    /**
     * Get folders from user selection
     * @return array
     */
    public function selectFromList()
    {
        $folders = [];
        $list = glob($this->src . '/*', GLOB_ONLYDIR);

        foreach ($list as $key => $path) {
            $list[$key] = basename($path);
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
}
