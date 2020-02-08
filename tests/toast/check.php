<?php

/** Generic tests for checks */
return function () : Generator {
    $object = new class extends Monomelodies\Codein\Check {
        public function check(string $file) : Generator
        {
            if ($file != '../files/dummy.php') {
                yield 'wrong file!';
            }
            return;
        }
    };

    /** Running a check yields a generator, but with no errors for this test case */
    yield function () use ($object) {
        $result = $object->check('../files/dummy.php');
        assert($result instanceof Generator);
        $i = 0;
        foreach ($result as $error) {
            $i++;
        }
        assert($i === 0);
    };

};

