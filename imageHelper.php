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

    // create needed classes
    $driver    = (!extension_loaded('imagick')) ? 'gd' : 'imagick';
    $args      = new Arguments($argv);
    $container = new Container();
    $app       = new Application($args);
    $files     = new FileHandler($args, new \Intervention\Image\ImageManager(['driver' => $driver]));
    $command   = null;
    
    // bind optimizer to container
    $container->bind('Optimizer', function ($optipng, $jpegoptim, $gifsicle) {
        return new \Extlib\ImageOptimizer([
            \Extlib\ImageOptimizer::OPTIMIZER_OPTIPNG   => $optipng,
            \Extlib\ImageOptimizer::OPTIMIZER_JPEGOPTIM => $jpegoptim,
            \Extlib\ImageOptimizer::OPTIMIZER_GIFSICLE  => $gifsicle
        ]);
    });

    // Minify-Command
    if ($args->isCommand('minify')) {
        $command = new Commands\Minify($args, $files, $container);
    }

    // Thumbnail-Command
    if ($args->isCommand('thumbnail')) {
        $command = new Commands\Thumbnail($args, $files, $container);
    }

    // run app
    $app->run($command);
}
