<?php

namespace Monomelodies\Codein;

use ReflectionClass;
use Generator;
use Ansi;
use Throwable;

/**
 * Check if all properties on the class are correctly defined and nothing is
 * "set on the fly".
 */
class Properties extends Check
{
    /**
     * Run the check.
     *
     * @param string $file
     * @return Generator
     */
    public function check(string $file) : Generator
    {
        if (!($class = $this->extractClass($file))) {
            return;
        }
        $reflection = new ReflectionClass($class);
        if ($reflection->isAbstract()) {
            return;
        }
        if (!($instance = $this->getInstance($reflection))) {
            return;
        }
        $properties = [];
        foreach ($reflection->getProperties() as $property) {
            $properties[$property->name] = true;
        }
        foreach ($instance as $property => $value) {
            if (!isset($properties[$property])) {
                yield "<red>Property <darkRed>$class->$property <red>is added on the fly in <darkRed>{$this->file}";
            }
        }
    }

    /**
     * Internal helper to format random arguments as a string.
     *
     * @param mixed $arg
     * @return string
     */
    private function toString($arg) : string
    {
        if (is_null($arg)) {
            return 'null';
        }
        if (is_string($arg)) {
            return "'".addslashes($arg)."'";
        }
        if (is_array($arg)) {
            return $this->stringifyArray($arg);
        }
        if (is_bool($arg)) {
            return $arg ? 'true' : 'false';
        }
        // Prolly int or float?
        return $arg;
    }

    /**
     * Internal helper to stringify an array.
     *
     * @param array $arg
     * @return string
     */
    private function stringifyArray(array $arg) : string
    {
        $ret = '[';
        $i = 0;
        foreach ($arg as $key => $value) {
            if ($i) {
                $ret .= ', ';
            }
            $ret .= '"'.$key.'" => '.$this->toString($value);
            $i++;
        }
        $ret .= ']';
        return $ret;
    }
}

