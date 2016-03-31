<?php
namespace watoki\karma\implementations\aggregates;

class ObjectAggregateFactory extends GenericAggregateFactory {

    /**
     * @param object $command
     * @return string
     */
    public function handleMethod($command) {
        return parent::handleMethod($command) . $this->name($command);
    }

    /**
     * @param object $event
     * @return string
     */
    public function applyMethod($event) {
        return parent::applyMethod($event) . $this->name($event);
    }

    /**
     * @param object $command
     * @return mixed
     */
    public function getAggregateIdentifier($command) {
        return parent::getAggregateIdentifier(get_class($command));
    }

    /**
     * @param object $object
     * @return string
     */
    protected function name($object) {
        return (new \ReflectionClass($object))->getShortName();
    }
}