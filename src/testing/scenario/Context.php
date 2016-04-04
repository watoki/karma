<?php
namespace watoki\karma\testing\scenario;

use watoki\karma\command\EventListener;
use watoki\karma\stores\EventStore;

class Context {

    /** @var EventStore */
    private $store;
    /** @var EventListener[] */
    private $listeners;

    /**
     * @param EventStore $store
     * @param EventListener[] $listeners
     */
    public function __construct(EventStore $store, array $listeners) {
        $this->store = $store;
        $this->listeners = $listeners;
    }

    public function that($event, $aggregateIdentifier) {
        foreach ($this->listeners as $listener) {
            if ($listener->listensTo($event)) {
                $listener->on($event);
            }
        }

        $this->store->append($event, $aggregateIdentifier);
    }
}