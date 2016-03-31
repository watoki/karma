<?php
namespace watoki\karma\implementations;

use watoki\karma\Application;
use watoki\karma\command\AggregateFactory;
use watoki\karma\implementations\aggregates\GenericAggregateFactory;
use watoki\karma\implementations\projections\GenericProjectionFactory;
use watoki\karma\query\ProjectionFactory;
use watoki\karma\stores\EventStore;

class GenericApplication extends Application {

    /** @var callable */
    private $isCommandCallback;

    /**
     * @param EventStore $store
     * @param AggregateFactory|null $aggregates
     * @param ProjectionFactory|null $projections
     */
    public function __construct(EventStore $store, AggregateFactory $aggregates = null, ProjectionFactory $projections = null) {
        parent::__construct(
            $store,
            $aggregates ?: GenericAggregateFactory::genericRoot(),
            $projections ?: GenericProjectionFactory::genericProjection());
    }

    /**
     * @param mixed $commandOrQuery
     * @return boolean
     */
    protected function isCommand($commandOrQuery) {
        if (!$this->isCommandCallback) {
            return true;
        }
        return call_user_func($this->isCommandCallback, $commandOrQuery);
    }

    /**
     * @param callable $callback
     * @return $this
     */
    public function setCommandCondition(callable $callback) {
        $this->isCommandCallback = $callback;
        return $this;
    }

    /**
     * @param string $pattern
     * @return GenericApplication
     */
    public function setCommandPattern($pattern) {
        return $this->setCommandCondition(function ($command) use ($pattern) {
            return is_string($command) && preg_match($pattern, $command);
        });
    }

    /**
     * @param string $pattern
     * @return GenericApplication
     */
    public function setCommandClassNamePattern($pattern) {
        return $this->setCommandCondition(function ($command) use ($pattern) {
            return is_object($command) && preg_match($pattern, get_class($command));
        });
    }

    /**
     * @param string $className
     * @return GenericApplication
     */
    public function setCommandBaseClass($className) {
        return $this->setCommandCondition(function ($command) use ($className) {
            return is_object($command) && is_subclass_of($command, $className);
        });
    }

    /**
     * @param string $className
     * @return GenericApplication
     */
    public function setQueryBaseClass($className) {
        return $this->setCommandCondition(function ($command) use ($className) {
            return !is_object($command) || !is_subclass_of($command, $className);
        });
    }
}