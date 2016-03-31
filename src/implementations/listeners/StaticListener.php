<?php
namespace watoki\karma\implementations\listeners;

use watoki\karma\command\EventListener;

class StaticListener implements EventListener {

    /** @var object */
    private $listener;

    /**
     * @param object $listener Defaults to self
     */
    public function __construct($listener = null) {
        $this->listener = $listener ?: $this;
    }

    /**
     * @param mixed $event
     * @return bool
     */
    public function listensTo($event) {
        return is_object($event) && method_exists($this->listener, $this->methodName($event));
    }

    /**
     * @param mixed $event
     * @return void
     */
    public function on($event) {
        call_user_func([$this->listener, $this->methodName($event)], $event);
    }

    /**
     * @param object $event
     * @return string
     */
    protected function name($event) {
        return (new \ReflectionClass($event))->getShortName();
    }

    /**
     * @param $event
     * @return string
     */
    private function methodName($event) {
        return 'on' . $this->name($event);
    }
}