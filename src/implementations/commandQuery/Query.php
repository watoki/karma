<?php
namespace watoki\karma\implementations\commandQuery;

interface Query {

    /**
     * @return object
     */
    public function getProjection();
}