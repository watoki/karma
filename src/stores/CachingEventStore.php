<?php
namespace watoki\karma\stores;

class CachingEventStore implements EventStore {

    /** @var mixed[][] */
    private static $cache = [];
    /** @var mixed[] */
    private static $all;
    /** @var EventStore */
    private $store;

    /**
     * @param EventStore $store
     */
    public function __construct(EventStore $store) {
        $this->store = $store;
    }

    /**
     * @return mixed[] All events appended to any aggregate
     */
    public function allEvents() {
        if (is_null(self::$all)) {
            self::$all = $this->store->allEvents();
        }
        return self::$all;
    }

    /**
     * @param mixed $aggregateIdentifier
     * @return mixed[] Events appended to aggregate
     */
    public function eventsOf($aggregateIdentifier) {
        if (!array_key_exists($aggregateIdentifier, self::$cache)) {
            self::$cache[$aggregateIdentifier] = $this->store->eventsOf($aggregateIdentifier);
        }
        return self::$cache[$aggregateIdentifier];
    }

    /**
     * @param mixed $event
     * @param mixed $aggregateIdentifier
     * @return void
     */
    public function append($event, $aggregateIdentifier) {
        $this->store->append($event, $aggregateIdentifier);
    }
}