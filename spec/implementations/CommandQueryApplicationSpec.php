<?php
namespace spec\watoki\karma\implementations;

use rtens\scrut\Assert;
use watoki\karma\Application;
use watoki\karma\implementations\commandQuery\Command;
use watoki\karma\implementations\commandQuery\CommandQueryApplication;
use watoki\karma\implementations\commandQuery\Query;
use watoki\karma\stores\EventStore;
use watoki\karma\stores\MemoryEventStore;

/**
 * @property Assert assert <-
 * @property Application application
 * @property EventStore store
 */
class CommandQueryApplicationSpec {

    function before() {
        CommandQueryApplicationSpec_AggregateRoot::$handled = [];
        CommandQueryApplicationSpec_Projection::$applied = [];

        $this->store = new MemoryEventStore();
        $this->application = new CommandQueryApplication($this->store);
    }

    function handleCommand() {
        $this->application->handle(new CommandQueryApplicationSpec_Command());

        $this->assert->equals($this->store->allEvents(),
            [new CommandQueryApplicationSpec_Event()]);
        $this->assert->equals(CommandQueryApplicationSpec_AggregateRoot::$handled,
            [new CommandQueryApplicationSpec_Command()]);
    }

    function projectQuery() {
        $this->store->append(new CommandQueryApplicationSpec_Event(), 'bar');
        $this->application->handle(new CommandQueryApplicationSpec_Query());

        $this->assert->equals(CommandQueryApplicationSpec_Projection::$applied,
            [new CommandQueryApplicationSpec_Event()]);
    }
}

class CommandQueryApplicationSpec_Command implements Command {

    public function getAggregateIdentifier() {
        return 'foo';
    }

    public function getAggregateRoot($identifier) {
        return new CommandQueryApplicationSpec_AggregateRoot($identifier);
    }
}

class CommandQueryApplicationSpec_AggregateRoot {
    public static $handled;

    public function handleCommandQueryApplicationSpec_Command(CommandQueryApplicationSpec_Command $command) {
        self::$handled[] = $command;
        return new CommandQueryApplicationSpec_Event();
    }
}

class CommandQueryApplicationSpec_Event {

}

class CommandQueryApplicationSpec_Query implements Query {

    public function getProjection() {
        return new CommandQueryApplicationSpec_Projection();
    }
}

class CommandQueryApplicationSpec_Projection {
    public static $applied;

    public function applyCommandQueryApplicationSpec_Event(CommandQueryApplicationSpec_Event $event) {
        self::$applied[] = $event;
    }
}