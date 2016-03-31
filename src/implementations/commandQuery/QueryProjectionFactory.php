<?php
namespace watoki\karma\implementations\commandQuery;

use watoki\karma\implementations\projections\ObjectProjectionFactory;

class QueryProjectionFactory extends ObjectProjectionFactory {

    public function __construct() {
        parent::__construct(function (Query $query) {
            return $query->getProjection();
        });
    }
}