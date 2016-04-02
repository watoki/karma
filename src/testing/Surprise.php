<?php
namespace watoki\karma\testing;

class Surprise {

    /** @var Expectation */
    private $expectation;

    /**
     * @param Expectation $expectation
     */
    public function __construct($expectation) {
        $this->expectation = $expectation;
    }

    function __call($name, $arguments) {
        try {
            call_user_func_array([$this->expectation, $name], $arguments);
        } catch (FailedExpectation $expected) {
            return;
        }

        throw new FailedExpectation("Expected [$name] to fail.");
    }
}