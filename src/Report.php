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
                $this->reflected->getStartLine(),
                $this->reflection->name,
                isset($this->reflected) ? $this->reflected->getName() : '',
                isset($this->reflected) ? $this->reflected->getName() : '',
            ],
            $this->message
        );
    }
}

