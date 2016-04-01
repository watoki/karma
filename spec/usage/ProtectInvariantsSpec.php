<?php
namespace spec\watoki\karma\usage;

use rtens\scrut\Failure;
use watoki\karma\implementations\commandQuery\Command;
use watoki\karma\implementations\commandQuery\CommandQueryApplication;
use watoki\karma\stores\MemoryEventStore;

class Foo4Aggregate {

    private $fooed = false;

    public function applyFooed4() {
        $this->fooed = true;
    }

    public function handleFoo4() {
        if ($this->fooed) {
            throw new \Exception('Foo me once shame on you, foo me twice shame on me');
        }
        return new Fooed4();
    }
}

class ProtectInvariantsSpec {

    function protectInvariants() {
        $application = new CommandQueryApplication(new MemoryEventStore());
        $application->handle(new Foo4());

        try {
            $application->handle(new Foo4());
        } catch (\Exception $e) {
            return;
        }

        throw new Failure('Should have thrown an exception');
    }
}

class Fooed4 {
}

class Foo4 implements Command {

    public function getAggregateIdentifier() {
        return 'foo';
    }

    public function getAggregateRoot() {
        return new Foo4Aggregate();
    }
}