<?php
namespace spec\watoki\karma\usage;

use watoki\karma\implementations\commandQuery\Command;
use watoki\karma\implementations\commandQuery\CommandQueryApplication;
use watoki\karma\stores\MemoryEventStore;

class FooAggregate {
    public static $handled = [];

    public function handleFoo(Foo $foo) {
        self::$handled[] = $foo;
    }
}

class HandleCommandsSpec {

    function handleCommands($assert) {
        $application = new CommandQueryApplication(new MemoryEventStore());
        $application->handle(new Foo('that'));

        $assert(FooAggregate::$handled, [new Foo('that')]);
    }
}

class Foo implements Command {

    public function getAggregateIdentifier() {
        return 'foo';
    }

    public function getAggregateRoot() {
        return new FooAggregate();
    }
}