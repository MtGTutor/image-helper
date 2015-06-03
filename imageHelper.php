<?php
/**
 * Simple CLI program to help getting the card images for MtG-Tutor ready
 * @author PascalKleindienst <mail@pascalkleindienst.de>
 * @version 1.0 
 */

/**
 * Constants
 */
define('VERSION', '1.0');

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
            elseif (count($args) && strpos($args[0], '-') !== 0) {
                while (strpos($args[0], '-') !== 0) {
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
    if (in_array('v', $args['flags']) || array_key_exists('version', $args['options'])) {
        echo 'imageHelper version ' . VERSION;
    }

    return 0;
}

if (php_sapi_name() == "cli") {
    main($argv);
}
