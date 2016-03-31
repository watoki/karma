<?php
namespace watoki\karma\implementations\commandQuery;

use watoki\karma\implementations\aggregates\ObjectAggregateFactory;

class CommandAggregateFactory extends ObjectAggregateFactory {

    public function __construct() {
        parent::__construct(function (Command $command) {
            return $command->getAggregateRoot();
        });
        $this->setGetAggregateIdentifierCallback(function (Command $command) {
            return $command->getAggregateIdentifier();
        });
    }
}