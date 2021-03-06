<?php
namespace watoki\karma\testing\scenario;

use watoki\karma\Application;
use watoki\karma\stores\EventStore;
use watoki\karma\testing\result\Outcome;
use watoki\karma\testing\result\FailedExpectation;
use watoki\karma\testing\result\MetExpectation;

class Expectation {

    /** @var Expectation */
    public $not;

    /** @var Application */
    private $application;
    /** @var EventStore */
    private $store;
    /** @var Outcome */
    private $outcome;

    /**
     * @param Application $application
     * @param EventStore $store
     * @param Outcome $outcome
     */
    public function __construct(Application $application, EventStore $store, Outcome $outcome) {
        $this->not = new Surprise($this);
        $this->application = $application;
        $this->store = $store;
        $this->outcome = $outcome;
    }

    public function shouldMatch(callable $condition, $message = 'Event was not appended') {
        $filtered = array_filter($this->store->allEvents(), $condition);

        if (!$filtered) {
            throw new FailedExpectation($message);
        }

        return new MetExpectation($filtered);
    }

    public function should($event) {
        return $this->shouldMatch(function ($found) use ($event) {
            return $found == $event;
        });
    }

    public function shouldMatchAll(callable $conditions) {
        $evaluatedConditions = $this->evaluateConditions($conditions);

        $allEvents = $this->store->allEvents();
        $matchingEvents = [];
        foreach ($evaluatedConditions as $i => $evaluatedCondition) {
            if ($evaluatedCondition === true) {
                $matchingEvents[] = $allEvents[$i];
            }
        }

        if (!$matchingEvents) {
            throw new FailedExpectation('No matching event found: ' . json_encode($evaluatedConditions));
        }

        return new MetExpectation($matchingEvents);
    }

    public function shouldFail($message = null) {
        if (!$this->outcome->caught) {
            throw new \Exception('No exception was thrown.');
        }

        if (!is_null($message) && $this->outcome->caught->getMessage() != $message) {
            throw new \Exception("Exception [{$this->outcome->caught->getMessage()}] should be [$message]");
        }
    }

    private function evaluateConditions(callable $conditions) {
        $evaluatedConditions = [];
        foreach ($this->store->allEvents() as $event) {
            $evaluatedConditions[] = $this->evaluateConditionsFor($conditions, $event);
        }
        return $evaluatedConditions;
    }


    private function evaluateConditionsFor(callable $conditions, $value) {
        $evaluated = [];

        foreach ($conditions($value) as $name => $condition) {
            if (is_array($condition) && count($condition) == 2 && $condition[0] != $condition[1]) {
                $evaluated[$name] = $condition[0];
            } else if (!$condition) {
                $evaluated[$name] = false;
            }
        }
        return $evaluated ?: true;
    }

    public function shouldMatchClass($class) {
        return $this->shouldMatch(function ($event) use ($class) {
            return is_object($event) && get_class($event) == $class;
        });
    }

    public function shouldMatchObject($class, callable $condition = null) {
        return $this->shouldMatch(function ($event) use ($class, $condition) {
            return is_object($event) && is_a($event, $class) && (!$condition || $condition($event));
        });
    }

    public function shouldMatchAllObject($class, callable $conditions) {
        return $this->shouldMatchAll(function ($event) use ($class, $conditions) {
            if (!is_object($event)) {
                return ['isObject' => false];
            } else if (!is_a($event, $class)) {
                return ['class' => [get_class($event), $class]];
            } else {
                return $conditions($event);
            }
        });
    }

    public function shouldReturn($projection) {
        $this->returnShouldMatch(function ($returned) use ($projection) {
            return $returned == $projection;
        });
    }

    public function returnShouldMatch(callable $condition) {
        if (!$condition($this->outcome->returned)) {
            throw new FailedExpectation('Unexpected return value');
        }
    }

    public function returnShouldMatchAll(callable $conditions) {
        $evaluatedConditions = $this->evaluateConditionsFor($conditions, $this->outcome->returned);

        if ($evaluatedConditions !== true) {
            throw new FailedExpectation('No matching event found: ' . json_encode($evaluatedConditions));
        }
    }
}