<?php

/** Testsuite for Monomelodies\Codein\Report */
return function () : Generator {
    $reflection = new ReflectionObject(new class {
        public function foo()
        {
        }
    });
    $method = $reflection->getMethod('foo');
    $object = new class($reflection, $method) extends Monomelodies\Codein\Report {
        protected $message = 'dummy';
    };
    /** getMessage yields $result === 'blarps' */
    yield function () use ($object) {
        $result = $object->getMessage();
        assert($result === 'dummy');
    };

};

