<?php

namespace Monomelodies\Codein\Test\Namespaces;

use Foo;

class CatchesException
{
    public function foo()
    {
        try {
        } catch (Foo $e) {
        }
    }
}

