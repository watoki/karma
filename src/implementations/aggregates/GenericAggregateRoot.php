<?php
namespace watoki\karma\implementations\aggregates;

class GenericAggregateRoot {

    /** @var callable|null */
    private $handleCallback;
    /** @var mixed[] */
    private $events = [];

    /**
     * @param callable|null $handleCallback Invoked with command and applied events
     */
    public function __construct(callable $handleCallback = null) {
        $this->handleCallback = $handleCallback;
    }

    public function handle($command) {
        if (!$this->handleCallback) {
            return null;
        }
        return call_user_func($this->handleCallback, $command, $this->events);
    }

    public function apply($event) {
        $this->events[] = $event;
    }
}