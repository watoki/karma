<?php
namespace watoki\karma\implementations\projections;

class ObjectProjectionFactory extends GenericProjectionFactory {

    /**
     * @param array $queryToProjectionMap
     * @return static
     */
    public static function mappedProjection(array $queryToProjectionMap) {
        return new static(function ($query) use ($queryToProjectionMap) {
            return $queryToProjectionMap[get_class($query)];
        });
    }

    /**
     * @param object $event
     * @return string
     */
    public function applyMethod($event) {
        return parent::applyMethod($event) . $this->name($event);
    }

    /**
     * @param object $event
     * @return string
     */
    protected function name($event) {
        return (new \ReflectionClass($event))->getShortName();
    }
}