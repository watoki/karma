<?php
namespace watoki\karma\implementations\commandQuery;

use watoki\karma\implementations\aggregates\ObjectAggregateFactory;

class CommandAggregateFactory extends ObjectAggregateFactory {

    public function __construct() {
        parent::__construct(function (Command $command, $identifier) {
            return $command->getAggregateRoot($identifier);
        });
    }

    /**
     * @param Command $command
     * @return mixed
     */
    public function getAggregateIdentifier($command) {
        return $command->getAggregateIdentifier();
    }
}