<?php
/**
 * Simple CLI program to help getting the card images for MtG-Tutor ready
 * @author PascalKleindienst <mail@pascalkleindienst.de>
 * @version 1.0 
 */
// check if vendor folder exists, otherwise run composer update
checkDependencies();

// include dependencies
require_once 'vendor/autoload.php';

use Extlib\ImageOptimizer;

/**
 * Constants
 */
define('VERSION', '1.0');
define('DEFAULT_SRC_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'src');
define('DEFAULT_DEST_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR. 'dest');

/**
 * @link http://php.net/manual/de/features.commandline.php#83843
 * @param array $args arguments
 * @return array
 */
function arguments(array $args = [])
{
    array_shift($args);
    $endofoptions = false;

    $return = [
      'commands'  => [],
      'options'   => [],
      'flags'     => [],
      'arguments' => [],
    ];

    while ($arg = array_shift($args)) {
        // if we have reached end of options, we cast all remaining argvs as arguments
        if ($endofoptions) {
            $return['arguments'][] = $arg;
            continue;
        }

        // Is it a command? (prefixed with --)
        if (substr($arg, 0, 2) === '--') {
            // is it the end of options flag?
            if (!isset ($arg[3])) {
                $endofoptions = true; // end of options;
                continue;
            }

            $value = '';
            $com   = substr($arg, 2);

            // is it the syntax '--option=argument'?
            if (strpos($com, '=')) {
                list($com, $value) = explode("=", $com, 2);
            }
            // or is the option not followed by another option but by arguments?
            elseif (count($args) > 0 && strpos($args[0], '-') !== 0) {
                while (count($args) > 0 && strpos($args[0], '-') !== 0) {
                    $value .= array_shift($args).' ';
                }
                
                $value = rtrim($value, ' ');
            }

            $return['options'][$com] = !empty($value) ? $value : true;
            continue;
        }

        // Is it a flag or a serial of flags? (prefixed with -)
        if (substr($arg, 0, 1) === '-') {
            for ($i = 1; isset($arg[$i]); $i++) {
                $return['flags'][] = $arg[$i];
            }
            continue;
        }

        // finally, it is not option, nor flag, nor argument
        $return['commands'][] = $arg;
        continue;
    }

    if (!count($return['options']) && !count($return['flags'])) {
        $return['arguments'] = array_merge($return['commands'], $return['arguments']);
        $return['commands'] = [];
    }

    return $return;
}

/**
 * check if a flag is set or not
 * @param  string  $flag   flag
 * @param  array  &$flags  Flags
 * @return boolean
 */
function isFlagSet($flag, &$flags)
{
    return in_array($flag, $flags);
}

/**
 * check if option exists and has a specific value
 * @param  string $key      option key
 * @param  string $value    value to check
 * @param  array &$options  options from arguments
 * @return boolean
 */
function optionEquals($key, $value, &$options)
{
    return array_key_exists($key, $options) && $options[$key] === $value;
}

/**
 * check for command
 * @param  string  $command 
 * @param  array  &$command 
 * @return boolean
 */
function isCommand($command, &$args)
{
    if (isArgument($command, $args['arguments'])) {
        $key = array_search($command, $args['arguments']);
        unset($args['arguments'][$key]);

        $args['commands'][] = $command;
    }

    return in_array($command, $args['commands']);
}

/**
 * check for argument
 * @param  string  $arg 
 * @param  array  &$args 
 * @return boolean
 */
function isArgument($arg, &$args)
{
    return in_array($arg, $args);
}

/**
 * Get Path to optimizer binary
 * @param  string $optimizer which optimizer
 * @return string
 */
function getOptimizerPath($optimizer)
{
    $whichOS      = strtolower(php_uname('s'));
    $path         = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'optimizers' . DIRECTORY_SEPARATOR;
    $usedOs       = 'linux';
    $architecture = 'x86';

    // check for os
    if (strpos($whichOS, 'windows') !== false) {
        $usedOs = 'win';
        $optimizer .= '.exe';
    }

    // check for 64 bit
    if (PHP_INT_SIZE === 8) { #64bit
        $architecture = 'x64';
    }

    // return path
    return $path . $usedOs. DIRECTORY_SEPARATOR . $architecture . DIRECTORY_SEPARATOR . $optimizer;
}

/**
 * checks if composer dependencies are loaded, if not runs composer update
 * @return void
 */
function checkDependencies()
{
    if (!file_exists('vendor') || !file_exists('composer.lock')) {
        echo 'Load missing dependencies: ' . PHP_EOL;
        system('composer update');
    }
}

/**
 * Get folders from user selection
 * @return array
 */
function selectFromList($dir)
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
        $line = intval(trim($line));

        // process selection
        if (array_key_exists($line-1, $list)) {
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
 * Main Entry point
 * @param array $args
 * @return int
 */
function main(array $args = [])
{
    // no arguments (except for imageHelper) => display help text
    if (count($args) == 1) {
        echo "usage: imageHelper [--version] [--src-dir=<path>] [--dest-dir=<path>] " . PHP_EOL
            . "                   [--keep-old] [-k] [-v] <command> [<args>]";
    }

    // extract commands, flags, options and arguments
    $args = arguments($args);

    // display version
    if (isFlagSet('v', $args['flags']) || optionEquals('version', true, $args['options'])) {
        echo 'imageHelper version ' . VERSION;
    }

    // Minify-Task
    if (isCommand('minify', $args)) {        
        // vars
        $folders = $args['arguments'];
        $srcDir  = isFlagSet('src-dir', $args['flags']) ? $args['flags']['src-dir'] : DEFAULT_SRC_DIR;
        $destDir = isFlagSet('dest-dir', $args['flags']) ? $args['flags']['dest-dir'] : DEFAULT_DEST_DIR;

        // if no folders are specified - display available list from --src-dir to select from
        if (empty($folders)) {
            $folders = selectFromList($srcDir);
        }

        //@TODO: minify
        // $optimizer = new ImageOptimizer([
        //     ImageOptimizer::OPTIMIZER_OPTIPNG   => getOptimizerPath('optipng'),
        //     ImageOptimizer::OPTIMIZER_JPEGOPTIM => getOptimizerPath('jpegoptim'),
        //     ImageOptimizer::OPTIMIZER_GIFSICLE  => getOptimizerPath('gifsicle')
        // ]);

        // $optimizer->optimize(dirname(__FILE__) . "/test.jpg"); //return true
    }
    
    return 0;
}

if (php_sapi_name() == "cli") {
    main($argv);
}
