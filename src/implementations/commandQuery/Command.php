<?php
namespace watoki\karma\implementations\commandQuery;

interface Command {

    /**
     * @return mixed
     */
    public function getAggregateIdentifier();

    /**
     * @param mixed $identifier
     * @return object
     */
    public function getAggregateRoot($identifier);
}