<?php
namespace watoki\karma;

use watoki\karma\command\AggregateFactory;
use watoki\karma\command\CommandHandler;
use watoki\karma\command\EventListener;
use watoki\karma\query\ProjectionFactory;
use watoki\karma\query\QueryProjector;
use watoki\karma\stores\EventStore;

abstract class Application {

    /** @var CommandHandler */
    private $handler;
    /** @var QueryProjector */
    private $projector;

    /**
     * @param EventStore $store
     * @param AggregateFactory $aggregates
     * @param ProjectionFactory $projections
     */
    public function __construct(EventStore $store, AggregateFactory $aggregates, ProjectionFactory $projections) {
        $this->handler = new CommandHandler($store, $aggregates);
        $this->projector = new QueryProjector($store, $projections);
    }

    /**
     * @param EventListener $listener
     * @return Application
     */
    public function addListener(EventListener $listener) {
        $this->handler->addListener($listener);
        return $this;
    }

    /**
     * @param mixed $commandOrQuery
     * @return null|object
     * @throws \Exception
     */
    public function handle($commandOrQuery) {
        if ($this->isCommand($commandOrQuery)) {
            $this->handler->handle($commandOrQuery);
            return null;
        } else {
            return $this->projector->project($commandOrQuery);
        }
    }

    /**
     * @param mixed $commandOrQuery
     * @return boolean
     */
    protected abstract function isCommand($commandOrQuery);
}