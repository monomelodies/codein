<?php

namespace Monomelodies\Codein;

use Monolyth\Cliff;
use Ansi;

/**
 * The main sensi\codein command to check for code smells.
 */
class Command extends Cliff\Command
{
    /** @var array */
    public $check = [];

    /** @var bool */
    public $recursive = false;

    /** @var int */
    private $errs = 0;

    /**
     * For the specified $dir, traverse all PHP files and subdirectories to
     * analyze the code contained.
     *
     * @param string $dir
     * @return void
     */
    public function __invoke(string $dir) : void
    {
        if (file_exists(getcwd().'/codein.json')) {
            $config = json_decode(file_get_contents(getcwd().'/codein.json'));
            if (isset($config->bootstrap)) {
                $files = is_array($config->bootstrap) ? $config->bootstrap : [$config->bootstrap];
                foreach ($files as $file) {
                    require_once getcwd()."/$file";
                }
            }
            if (isset($config->checks)) {
                $this->check = array_unique(array_merge($config->checks, $this->check));
            }
        }
        array_walk($this->check, function (&$check) : void {
            $class = $this->optionToClassName($check);
            $check = new $class;
        });
        $errs = $this->walk($dir);
        if (isset($errs)) {
            if (!$errs) {
                fwrite(STDOUT, Ansi::tagsToColors("<green>Everything okay!<reset>\n"));
            } elseif ($errs === 1) {
                fwrite(STDOUT, Ansi::tagsToColors("\n<reset>Found <bold>$errs <reset>code smell.\n"));
            } else {
                fwrite(STDOUT, Ansi::tagsToColors("\n<reset>Found <bold>$errs <reset>code smells.\n"));
            }
            fwrite(STDOUT, "\n");
        }
    }

    /**
     * Check a file or directory, optionally using recursion.
     *
     * @param string $dir
     * @return int|null The number of errors found, or null if no tests were
     *  defined (see the options for the command).
     */
    private function walk(string $dir) :? int
    {
        if (!$this->check) {
            fwrite(STDERR, Ansi::tagsToColors("\n<red>No checks specified!<reset>\n\n"));
            return null;
        }
        if (!file_exists($dir)) {
            fwrite(STDERR, Ansi::tagsToColors("\n<red><bold>$dir <reset><red>: no such file or directory.<reset>\n"));
            return null;
        }
        if (is_file($dir)) {
            $this->checkFile($dir);
            return $this->errs;
        }
        $d = dir($dir);
        while (false !== ($entry = $d->read())) {
            if ($entry{0} == '.') {
                continue;
            }
            if (is_dir("$dir/$entry") && $this->recursive) {
                $this->errs += $this->walk("$dir/$entry");
                continue;
            }
            if (!preg_match("@\.php$@", $entry)) {
                continue;
            }
            $this->checkFile("$dir/$entry");
        }
        return $this->errs;
    }

    /**
     * Check a single file.
     *
     * @param string $file
     * @return void
     */
    private function checkFile(string $file) : void
    {
        try {
            include $file;
        } catch (Throwable $e) {
            ++$this->errs;
            $error = $e->getMessage();
            fwrite(STDERR, Ansi::tagsToColors("\n<red><bold>$file <reset><red>: error parsing file: <bold>$error<reset>\n"));
        }
        foreach ($this->check as $errors) {
            foreach ($errors->check($file) as $error) {
                ++$this->errs;
                fwrite(STDOUT, Ansi::tagsToColors("$error<reset>\n"));
            }
        }
    }

    /**
     * Convert a --check=... option to a valid classname.
     */
    private function optionToClassName(string $option) : string
    {
        $option = ucfirst($option);
        $option = preg_replace_callback('@-([a-z])@', function ($match) {
            return strtoupper($match[1]);
        }, $option);
        $option = preg_replace_callback('@/([a-z])@', function ($match) {
            return '\\'.strtoupper($match[1]);
        }, $option);
        return $option;
    }
}

