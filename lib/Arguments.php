<?php namespace MtGTutor\CLI\ImageHelper;

/**
 * Simple Class to handle arguments
 * @author PascalKleindienst <mail@pascalkleindienst.de>
 * @version 1.0
 * 
 * @method boolean isFlagSet(string $flag, array $flags)
 * @method boolean isArgumentSet(string $arg, array $args)
 * @method boolean optionEquals(string $option, mixed $equals, array $options)
 * @method boolean isCommand(string $command, array $commands)
 */
class Arguments implements \ArrayAccess
{
    /**
     * @var array
     */
    private $container = [];

    /**
     * @param array $args
     */
    public function __construct(array $args = [])
    {
        $this->container = $this->arguments($args);
    }

    /**
     * Check if there are any args at all
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->container['commands']) && empty($this->container['options'])
            && empty($this->container['flags']) && empty($this->container['arguments']);
    }

    /**
     * Checks for flags, arguments, options and commands
     * @param  string $method
     * @param  array $args
     * @return boolean|null
     */
    public function __call($method, $args)
    {
        $arg = null;

        // two args
        if (count($args) === 2) {
            $arg = $args[0];
            $value = $args[1];
        }

        // one arg
        if (count($args) === 1) {
            $arg = $args[0];
            $value = null;
        }

        switch ($method) {
            // check if a flag is set or not
            case 'isFlagSet':
                return in_array($arg, $this['flags']);
            // check if an argument is set or not
            case 'isArgumentSet':
                return in_array($arg, $this['arguments']);
            // check if option equals specific value
            case 'optionEquals':
                return array_key_exists($arg, $this['options']) && $this['options'][$arg] === $value;
            // check if option equals specific value
            case 'isCommand':
                if ($this->isArgumentSet($arg, $this['arguments'])) {
                    $key = array_search($arg, $this['arguments']);
                    unset($this->container['arguments'][$key]);

                    $this->container['commands'][] = $arg;
                }

                return in_array($arg, $this['commands']);
        }

        return null;
    }

    /**
     * @link http://php.net/manual/de/features.commandline.php#83843
     * @param array $args arguments
     * @return array
     */
    protected function arguments(array $args = [])
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
                } // or is the option not followed by another option but by arguments?
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
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
            return;
        }
            
        $this->container[$offset] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }
}
