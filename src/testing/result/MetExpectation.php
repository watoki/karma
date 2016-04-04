<?php
namespace watoki\karma\testing\result;

class MetExpectation {

    /** @var mixed[] */
    private $matchingEvents;

    /**
     * @param mixed[] $matchingEvents
     */
    public function __construct($matchingEvents) {
        $this->matchingEvents = $matchingEvents;
    }

    public function count($int) {
        $count = count($this->matchingEvents);
        if ($count != $int) {
            throw new FailedExpectation("Number of matching events should be [$int] but is [$count]");
        }
    }
}