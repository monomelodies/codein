<?php

namespace Sensi\Codein;

use Generator;
use ReflectionClass;
use Ansi;
use Throwable;

/**
 * Abstract base check our other checks should extend.
 */
abstract class Check
{
    /** @var string */
    protected $file;

    /** @var string */
    protected $code;

    /**
     * @param string $file
     * @return void
     */
    protected function initialize(string $file) : void
    {
        $this->file = $file;
        $this->code = preg_replace("@/\*(.*?)\*/@ms", '', file_get_contents($file));
    }

    /**
     * Extract a classname from the given file.
     *
     * @param string $file
     * @return string|null The classname, or null if the file does not contain
     *  a class.
     */
    protected function extractClass(string $file) :? string
    {
        $this->initialize($file);
        $namespace = null;
        if (preg_match('@^namespace ([A-Za-z][A-Za-z0-9\\\\_]*);$@m', $this->code, $matches)) {
            $namespace = $matches[1];
        }
        $classname = null;
        if (preg_match('@^((final|abstract) )?class ([A-Za-z][A-Za-z0-9\\\\_]*)(\s|$)@m', $this->code, $matches)) {
            $classname = $matches[3];
        }
        if (!isset($classname)) {
            return null;
        }
        return isset($namespace) ? "$namespace\\$classname" : $classname;
    }

    /**
     * Create an instance of the passed ReflectionClass.
     *
     * @return object|null
     */
    protected function getInstance(ReflectionClass $reflection) :? object
    {
        $args = [];
        if (file_exists(getcwd().'/codein.json')) {
            $config = json_decode(file_get_contents(getcwd().'/codein.json'), true);
        }
        if (isset($config, $config['constructors'], $config['constructors'][$class])) {
            foreach ($config['constructors'][$class] as $argument) {
                $args[] = eval("return $argument;");
            }
        } elseif ($constructor = $reflection->getConstructor()) {
            $cached = [];
            foreach ($constructor->getParameters() as $parameter) {
                if ($parameter->isDefaultValueAvailable()) {
                    $args[] = $parameter->getDefaultValue();
                    $cached[] = $this->toString($parameter->getDefaultValue());
                } else {
                    fwrite(STDOUT, Ansi::tagsToColors("<darkGreen>$class<green> constructor argument <darkGreen>\${$parameter->name}<green> value: <reset>\n"));
                    $argument = trim(fgets(STDIN));
                    $args[] = strlen($argument) ? eval("return $argument;") : null;
                    $cached[] = strlen($argument) ? $argument : 'null';
                }
            }
        }
        try {
            return $reflection->newInstance(...$args);
        } catch (Throwable $e) {
            fwrite(STDERR, Ansi::tagsToColors("<red>Could not construct <darkRed>$class <red>from <darkRed>{$this->file}; specify construction arguments manually in <darkRed>codein.json<reset>\n"));
            if (isset($config)) {
                $config['constructors'] = $config['constructors'] ?? [];
                $config['constructors'][$class] = $cached;
                file_put_contents(getcwd().'/codein.json', json_encode($config, JSON_PRETTY_PRINT));
            }
        }
    }

    /**
     * Do the actual check. Checks should implement this.
     *
     * @param string $file
     * @return Generator
     */
    public abstract function check(string $file) : Generator;
}

