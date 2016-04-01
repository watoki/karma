<?php
namespace spec\watoki\karma\usage;

use watoki\karma\implementations\commandQuery\CommandQueryApplication;
use watoki\karma\implementations\commandQuery\Query;
use watoki\karma\stores\MemoryEventStore;

class BarProjection {
    public $fooed = [];

    public function applyFooed5(Fooed5 $fooed) {
        $this->fooed[] = 'So ' . $fooed->how;
    }
}

class ProjectEventsSpec {

    function projectEvents($assert) {
        $store = new MemoryEventStore();
        $application = new CommandQueryApplication($store);

        $store->append(new Fooed5('that happened'), 'foo');
        $bar = $application->handle(new Bar());

        $assert($bar->fooed, ['So that happened']);
    }
}

class Bar implements Query {

    public function getProjection() {
        return new BarProjection();
    }
}

class Fooed5 {
    public $how;

    public function __construct($how) {
        $this->how = $how;
    }
}