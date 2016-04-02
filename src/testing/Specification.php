<?php
namespace watoki\karma\testing;

use watoki\karma\Application;
use watoki\karma\command\EventListener;
use watoki\karma\implementations\aggregates\GenericAggregateFactory;
use watoki\karma\stores\EventStore;
use watoki\karma\stores\MemoryEventStore;

class Specification {

    /** @var Application */
    private $application;
    /** @var EventStore */
    private $store;

    /** @var Action */
    public $when;
    /** @var Expectation */
    public $then;
    /** @var Context */
    public $given;

    /**
     * @param callable $applicationBuilder
     * @param EventListener[] $listeners
     */
    public function __construct(callable $applicationBuilder, array $listeners = []) {
        $this->store = new MemoryEventStore();
        $this->application = $applicationBuilder($this->store);

        foreach ($listeners as $listener) {
            $this->application->addListener($listener);
        }

        $outcome = new Outcome();
        $this->given = new Context($this->store, $listeners);
        $this->when = new Action($this->application, $outcome);
        $this->then = new Expectation($this->application, $this->store, $outcome);
    }

    public function appendedEvents() {
        return $this->store->allEvents();
    }

    public function given($event, $aggregateIdentifier = GenericAggregateFactory::DEFAULT_IDENTIFIER) {
        $this->given->that($event, $aggregateIdentifier);
    }

    public function when($commandOrQuery) {
        $this->when->__invoke($commandOrQuery);
    }

    public function then($event) {
        $this->then->should($event);
    }
}