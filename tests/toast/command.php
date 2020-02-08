<?php

use Gentry\Gentry\Wrapper;

/** Test the command itself */
return function () : Generator {
    $object = Wrapper::createObject(Monomelodies\Codein\Command::class, ['--silent', 'tests/files/dummy.php']);
    /** Given the correct parameters, the command can be invoked */
    yield function () use ($object) {
        $result = $object->__invoke('tests/files/dummy.php');
        assert($result === null);
    };

};

