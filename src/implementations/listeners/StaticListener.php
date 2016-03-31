<?php
namespace watoki\karma\implementations\listeners;

use watoki\karma\command\EventListener;

class StaticListener implements EventListener {

    /**
     * @param mixed $event
     * @return bool
     */
    public function listensTo($event) {
        return is_object($event) && method_exists($this, $this->methodName($event));
    }

    /**
     * @param mixed $event
     * @return void
     */
    public function on($event) {
        call_user_func([$this, $this->methodName($event)], $event);
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