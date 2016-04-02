<?php
namespace watoki\karma\testing;

class Outcome {

    /** @var \Exception|null */
    public $caught;

    /** @var mixed */
    public $returned;

    public function reset() {
        $this->caught = null;
        $this->returned = null;
    }

    public function failed(\Exception $exception) {
        $this->caught = $exception;
    }
}