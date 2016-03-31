<?php
namespace watoki\karma\implementations\commandQuery;

interface Command {

    /**
     * @return mixed
     */
    public function getAggregateIdentifier();

    /**
     * @return object
     */
    public function getAggregateRoot();
}