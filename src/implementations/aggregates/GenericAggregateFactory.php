<?php
namespace watoki\karma\implementations\aggregates;

use watoki\karma\command\AggregateFactory;

class GenericAggregateFactory implements AggregateFactory {

    const DEFAULT_IDENTIFIER = 'karma';

    /** @var callable */
    private $buildAggregateRootCallback;
    /** @var callable */
    private $getAggregateIdentifierCallback;

    /**
     * @param callable $buildAggregateRoot
     */
    public function __construct(callable $buildAggregateRoot) {
        $this->buildAggregateRootCallback = $buildAggregateRoot;
    }

    /**
     * @param object $aggregateRoot
     * @return static
     */
    public static function staticRoot($aggregateRoot) {
        return new static(function () use ($aggregateRoot) {
            return $aggregateRoot;
        });
    }

    /**
     * @param callable|null $handleCallback
     * @return static
     */
    public static function genericRoot(callable $handleCallback = null) {
        return self::staticRoot(new GenericAggregateRoot($handleCallback));
    }

    /**
     * @param array $identifierToAggregateRootMap
     * @return static
     */
    public static function mappedRoot(array $identifierToAggregateRootMap) {
        return new static(function ($command) use ($identifierToAggregateRootMap) {
            return $identifierToAggregateRootMap[$command];
        });
    }

    /**
     * @param mixed $command
     * @return string
     */
    public function handleMethod($command) {
        return 'handle';
    }

    /**
     * @param mixed $event
     * @return string
     */
    public function applyMethod($event) {
        return 'apply';
    }

    /**
     * @param mixed $command
     * @return mixed
     */
    public function getAggregateIdentifier($command) {
        return isset($this->getAggregateIdentifierCallback)
            ? call_user_func($this->getAggregateIdentifierCallback, $command)
            : self::DEFAULT_IDENTIFIER;
    }

    /**
     * @param callable $getAggregateIdentifierCallback
     * @return GenericAggregateFactory
     */
    public function setGetAggregateIdentifierCallback(callable $getAggregateIdentifierCallback) {
        $this->getAggregateIdentifierCallback = $getAggregateIdentifierCallback;
        return $this;
    }

    /**
     * @param mixed $command
     * @return object
     */
    public function buildAggregateRoot($command) {
        return call_user_func($this->buildAggregateRootCallback, $command);
    }
}