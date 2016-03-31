<?php
namespace watoki\karma\implementations\projections;

class GenericProjection {

    private $events = [];

    /**
     * @return mixed[]
     */
    public function getEvents() {
        return $this->events;
    }

    public function apply($event) {
        $this->events[] = $event;
    }
}