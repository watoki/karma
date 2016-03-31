<?php
namespace watoki\karma\implementations\aggregates;

use watoki\karma\command\AggregateFactory;

class CascadingAggregateFactory implements AggregateFactory {

    /** @var AggregateFactory */
    private $first;
    /** @var AggregateFactory */
    private $second;

    /**
     * @param AggregateFactory $first
     * @param AggregateFactory $second
     */
    public function __construct(AggregateFactory $first, AggregateFactory $second) {
        $this->first = $first;
        $this->second = $second;
    }

    /**
     * @param mixed $command
     * @return string
     */
    public function handleMethod($command) {
        return $this->first->handleMethod($command)
            ?: $this->second->handleMethod($command);
    }

    /**
     * @param mixed $event
     * @return string
     */
    public function applyMethod($event) {
        return $this->first->applyMethod($event)
            ?: $this->second->applyMethod($event);
    }

    /**
     * @param object $command
     * @return mixed
     */
    public function getAggregateIdentifier($command) {
        return $this->first->getAggregateIdentifier($command)
            ?: $this->second->getAggregateIdentifier($command);
    }

    /**
     * @param mixed $command
     * @param mixed $identifier
     * @return object
     */
    public function buildAggregateRoot($command, $identifier) {
        return $this->first->buildAggregateRoot($command, $identifier)
            ?: $this->second->buildAggregateRoot($command, $identifier);
    }
}