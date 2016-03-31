<?php
namespace watoki\karma\implementations\listeners;

use watoki\karma\command\EventListener;

class CallbackListener implements EventListener {

    /** @var callable */
    private $listensToCallback;
    /** @var callable */
    private $onCallback;

    /**
     * @param callable $callback
     * @param callable|null $filter
     */
    public function __construct(callable $callback, callable $filter = null) {
        $this->listensToCallback = $filter;
        $this->onCallback = $callback;
    }

    /**
     * @param mixed $event
     * @return bool
     */
    public function listensTo($event) {
        return $this->listensToCallback
            ? call_user_func($this->listensToCallback, $event)
            : true;
    }

    /**
     * @param mixed $event
     * @return void
     */
    public function on($event) {
        call_user_func($this->onCallback, $event);
    }
}