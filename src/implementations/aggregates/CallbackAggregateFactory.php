<?php
namespace watoki\karma\implementations\aggregates;

use watoki\karma\command\AggregateFactory;

class CallbackAggregateFactory implements AggregateFactory {

    /** @var callable */
    private $handleMethodCallback;
    /** @var callable */
    private $applyMethodCallback;
    /** @var callable */
    private $getAggregateIdentifierCallback;
    /** @var callable */
    private $buildAggregateRootCallback;

    /**
     * @param mixed $command
     * @return string
     */
    public function handleMethod($command) {
        return $this->handleMethodCallback
            ? call_user_func($this->handleMethodCallback, $command)
            : null;
    }

    /**
     * @param mixed $event
     * @return string
     */
    public function applyMethod($event) {
        return $this->applyMethodCallback
            ? call_user_func($this->applyMethodCallback, $event)
            : null;
    }

    /**
     * @param object $command
     * @return mixed
     */
    public function getAggregateIdentifier($command) {
        return $this->getAggregateIdentifierCallback
            ? call_user_func($this->getAggregateIdentifierCallback, $command)
            : null;
    }

    /**
     * @param mixed $command
     * @param mixed $identifier
     * @return object
     */
    public function buildAggregateRoot($command, $identifier) {
        return $this->buildAggregateRootCallback
            ? call_user_func($this->buildAggregateRootCallback, $command, $identifier)
            : null;
    }

    /**
     * @param callable $buildAggregateRootCallback
     * @return CallbackAggregateFactory
     */
    public function setBuildAggregateRootCallback($buildAggregateRootCallback) {
        $this->buildAggregateRootCallback = $buildAggregateRootCallback;
        return $this;
    }

    /**
     * @param callable $handleMethodCallback
     * @return CallbackAggregateFactory
     */
    public function setHandleMethodCallback($handleMethodCallback) {
        $this->handleMethodCallback = $handleMethodCallback;
        return $this;
    }

    /**
     * @param callable $applyMethodCallback
     * @return CallbackAggregateFactory
     */
    public function setApplyMethodCallback($applyMethodCallback) {
        $this->applyMethodCallback = $applyMethodCallback;
        return $this;
    }

    /**
     * @param callable $getAggregateIdentifierCallback
     * @return CallbackAggregateFactory
     */
    public function setGetAggregateIdentifierCallback($getAggregateIdentifierCallback) {
        $this->getAggregateIdentifierCallback = $getAggregateIdentifierCallback;
        return $this;
    }
}