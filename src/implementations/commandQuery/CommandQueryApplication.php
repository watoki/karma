<?php
namespace watoki\karma\implementations\commandQuery;

use watoki\karma\implementations\GenericApplication;
use watoki\karma\stores\EventStore;

class CommandQueryApplication extends GenericApplication {

    public function __construct(EventStore $store) {
        parent::__construct($store, new CommandAggregateFactory(), new QueryProjectionFactory());

        $this->setCommandCondition(function ($commandOrQuery) {
            return $commandOrQuery instanceof Command;
        });
    }
}