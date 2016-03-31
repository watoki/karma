<?php
namespace watoki\karma\implementations\listeners;

class ObjectListener extends CallbackListener {

    /**
     * @param callable $callback
     * @param string $class
     */
    public function __construct(callable $callback, $class) {
        parent::__construct($callback, function ($event) use ($class) {
            return is_object($event) && get_class($event) == $class;
        });
    }
}