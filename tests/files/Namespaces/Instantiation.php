<?php

namespace Monomelodies\Codein\Tests\Namespaces;

use Foo;

class Instantiation
{
    public function __construct()
    {
        $foo = new Foo;
    }
}

