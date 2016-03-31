<?php
namespace watoki\karma\implementations\aggregates;

use watoki\karma\command\AggregateFactory;

class GenericAggregateFactory implements AggregateFactory {

    /** @var callable */
    private $buildAggregateRootCallback;
    /** @var array */
    private $commandIdentifierMap = [];

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
        /** @noinspection PhpUnusedParameterInspection */
        return new static(function ($command, $identifier) use ($identifierToAggregateRootMap) {
            return $identifierToAggregateRootMap[$identifier];
        });
    }

    /**
     * @param mixed $command
     * @param mixed $identifier
     * @return GenericAggregateFactory
     */
    public function mapCommandToIdentifier($command, $identifier) {
        $this->commandIdentifierMap[(string)$command] = $identifier;
        return $this;
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
        return isset($this->commandIdentifierMap[(string)$command])
            ? $this->commandIdentifierMap[(string)$command]
            : $command;
    }

    /**
     * @param mixed $command
     * @param mixed $identifier
     * @return object
     */
    public function buildAggregateRoot($command, $identifier) {
        return call_user_func($this->buildAggregateRootCallback, $command, $identifier);
    }
}