<?php
namespace spec\watoki\karma\usage;

use watoki\karma\implementations\commandQuery\Command;
use watoki\karma\implementations\commandQuery\CommandQueryApplication;
use watoki\karma\implementations\listeners\StaticListener;
use watoki\karma\stores\MemoryEventStore;

class FooListener {
    public static $heard;

    public function onFooed3(Fooed3 $fooed) {
        self::$heard = $fooed;
    }
}

class ReactToEventsSpec {

    function reactToEvents($assert) {
        $application = new CommandQueryApplication(new MemoryEventStore());
        $application->addListener(new StaticListener(new FooListener()));
        $application->handle(new Foo3('this'));

        $assert(FooListener::$heard, new Fooed3('this happened'));
    }
}

class Fooed3 {

    public function __construct($how) {
        $this->how = $how;
    }
}

class Foo3Aggregate {

    public function handleFoo3(Foo3 $foo) {
        return new Fooed3($foo->what . ' happened');
    }
}

class Foo3 implements Command {
    public $what;

    public function __construct($what) {
        $this->what = $what;
    }

    public function getAggregateIdentifier() {
        return 'foo';
    }

    public function getAggregateRoot() {
        return new Foo3Aggregate();
    }
}