<?php

namespace Monomelodies\Codein;

use ReflectionClass;
use Reflector;

abstract class Report
{
    /** @var string */
    protected $message;

    /** @var ReflectionClass */
    private $reflection;

    /** @var Reflector */
    private $reflected;

    /**
     * @param ReflectionClass $reflection
     * @param Reflector|null $reflected
     * @return void
     */
    public function __construct(ReflectionClass $reflection, Reflector $reflected = null)
    {
        $this->reflection = $reflection;
        $this->reflected = $reflected;
    }

    /**
     * Returns a message with placeholders replaced.
     *
     * @return string
     */
    public function getMessage() : string
    {
        return str_replace(
            ['{file}', '{line}', '{class}', '{method}', '{property}'],
            [
                $this->reflection->getFileName(),
                isset($this->reflected) ? $this->reflected->getStartLine() : 0,
                $this->reflection->name,
                isset($this->reflected) ? $this->reflected->getName() : '',
                isset($this->reflected) ? $this->reflected->getName() : '',
            ],
            $this->message
        );
    }

    /**
     * Attempt to fix the code smell in question. Custom reports must override
     * this in order to do something useful :)
     *
     * @return bool True if the fix was succesfull, else false.
     */
    public function fix() : bool
    {
        return false;
    }

    /**
     * Helper to get the code.
     *
     * @return string
     */
    protected function getCode() : string
    {
        return file_get_contents($this->reflection->getFileName());
    }

    /**
     * Helper to write the code.
     *
     * @param string $code
     * @return void
     */
    protected function writeCode(string $code) : void
    {
        file_put_contents($this->reflection->getFileName(), $code);
    }
}

