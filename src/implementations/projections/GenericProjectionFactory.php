<?php
namespace watoki\karma\implementations\projections;

use watoki\karma\query\ProjectionFactory;

class GenericProjectionFactory implements ProjectionFactory {

    /** @var callable */
    private $buildProjectionCallback;

    /**
     * @param callable $buildProjection Invoked with query
     */
    public function __construct(callable $buildProjection) {
        $this->buildProjectionCallback = $buildProjection;
    }

    /**
     * @param mixed $projection
     * @return GenericProjectionFactory
     */
    public static function staticProjection($projection) {
        return new static(function () use ($projection) {
            return $projection;
        });
    }

    /**
     * @return GenericProjectionFactory
     */
    public static function genericProjection() {
        return self::staticProjection(new GenericProjection());
    }

    /**
     * @param array $queryToProjectionMap
     * @return static
     */
    public static function mappedProjection(array $queryToProjectionMap) {
        return new static(function ($query) use ($queryToProjectionMap) {
            return $queryToProjectionMap[$query];
        });
    }

    /**
     * @param mixed $event
     * @return string
     */
    public function applyMethod($event) {
        return 'apply';
    }

    /**
     * @param mixed|null $query
     * @return object
     */
    public function buildProjection($query = null) {
        return call_user_func($this->buildProjectionCallback, $query);
    }
}