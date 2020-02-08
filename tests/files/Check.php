<?php

namespace Monomelodies\Codein\Test;

use Monomelodies\Codein;
use Generator;

class Check extends Codein\Check
{
    public function check(string $file) : Generator
    {
        if (false) {
            yield 'dummy';
        }
        return;
    }
}

