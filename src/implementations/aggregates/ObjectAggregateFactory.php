<?php
namespace watoki\karma\implementations\aggregates;

class ObjectAggregateFactory extends GenericAggregateFactory {

    /**
     * @param array $identifierToAggregateRootMap
     * @return static
     */
    public static function mappedRoot(array $identifierToAggregateRootMap) {
        return new static(function ($command) use ($identifierToAggregateRootMap) {
            return $identifierToAggregateRootMap[get_class($command)];
        });
    }

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
     * @param object $object
     * @return string
     */
    protected function name($object) {
        return (new \ReflectionClass($object))->getShortName();
    }
}