<?php namespace MtGTutor\CLI\ImageHelper;

/**
 * Simple CLI program to help getting the card images for MtG-Tutor ready
 * @author PascalKleindienst <mail@pascalkleindienst.de>
 * @version 1.0 
 */

// check if vendor folder exists, otherwise run composer update
if (!file_exists('vendor') || !file_exists('composer.lock')) {
    echo 'Load missing dependencies: ' . PHP_EOL;
    system('composer update');
}

if (php_sapi_name() == "cli") {
    // include dependencies
    require_once 'vendor/autoload.php';

    // create args
    $args = new Arguments($argv);
    $command = null;

    // Minify-Command
    // if ($args->isCommand('minify')) {
    //     $command = new Commands\Minify($args);
    // }

    // run app
    $app = new Application($args, $command);
    $app->run();
}
