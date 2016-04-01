<?php
namespace spec\watoki\karma\usage;

use watoki\karma\implementations\commandQuery\Command;
use watoki\karma\implementations\commandQuery\CommandQueryApplication;
use watoki\karma\stores\MemoryEventStore;

class Foo2Aggregate {

    public function handleFoo2(Foo2 $foo) {
        return new Fooed($foo->what . ' happened');
    }
}

class Fooed {
    private $how;

    public function __construct($how) {
        $this->how = $how;
    }
}

class StoreEventsSpec {

    function storeEvents($assert) {
        $store = new MemoryEventStore();
        $application = new CommandQueryApplication($store);
        $application->handle(new Foo2('that'));

        $assert($store->allEvents(), [new Fooed('that happened')]);
    }
}

class Foo2 implements Command {
    public $what;

    public function __construct($what) {
        $this->what = $what;
    }

    public function getAggregateIdentifier() {
        return 'foo';
    }

    public function getAggregateRoot() {
        return new Foo2Aggregate();
    }
}