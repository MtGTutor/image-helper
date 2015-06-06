<?php namespace MtGTutor\CLI\ImageHelper;

use ReflectionClass;

/**
 * Simple DI Container Class
 * @author PascalKleindienst <mail@pascalkleindienst.de>
 * @version 1.0
 */
class Container
{
    /**
     * @var array
     */
    private $container = [];

    /**
     * Add a service to the container
     * @param  string $name
     * @param  string|callable $service
     * @return void
     */
    public function bind($name, $service)
    {
        $this->container[$name] = $service;
    }

    /**
     * Return instance of service
     * @param  string $name
     * @param  mixed $args
     * @return mixed
     */
    public function resolve($name, $args)
    {
        if (array_key_exists($name, $this->container)) {
            // get args
            $args =func_get_args();
            array_shift($args);

            $service = $this->container[$name];

            // run cb function
            if (is_callable($service)) {
                return call_user_func_array($service, $args);
            }

            // create class instance
            if (class_exists($service)) {
                $ref = new ReflectionClass($service);
                return $ref->newInstanceArgs($args);
            }
        }

        return null;
    }
}
