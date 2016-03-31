<?php
namespace watoki\karma\testing;

use watoki\karma\command\EventListener;
use watoki\karma\stores\EventStore;
use watoki\karma\stores\MemoryEventStore;
use watoki\reflect\ValuePrinter;

abstract class Scenario {

    /** @var EventStore */
    private $events;
    /** @var mixed */
    private $returned;
    /** @var null|\Exception */
    private $caught;

    public function __construct() {
        $this->reset();
    }

    /**
     * @return EventListener[]
     */
    protected abstract function listeners();

    /**
     * @param EventStore $events
     * @param mixed $commandOrQuery
     * @return mixed
     */
    protected abstract function handle(EventStore $events, $commandOrQuery);

    /**
     * @return EventStore
     */
    protected function makeEventStore() {
        return new MemoryEventStore();
    }

    public function reset() {
        $this->events = $this->makeEventStore();
    }

    public function allEvents() {
        return $this->events->allEvents();
    }

    public function given($event, $aggregateIdentifier) {
        $this->events->append($event, $aggregateIdentifier);
    }

    public function when($message) {
        foreach ($this->events->allEvents() as $event) {
            foreach ($this->listeners() as $listener) {
                if ($listener->listensTo($event)) {
                    $listener->on($event);
                }
            }
        }
        $this->returned = $this->handle($this->events, $message);
    }

    public function tryTo($messageOrCallable) {
        $this->caught = null;
        try {
            if (is_callable($messageOrCallable)) {
                $messageOrCallable();
            } else {
                $this->when($messageOrCallable);
            }
        } catch (\Exception $e) {
            $this->caught = $e;
        }
    }

    public function thenShould($eventOrClass, callable $condition = null, $count = null) {
        $filtered = $this->mapFilters($eventOrClass, $condition);
        $evaluated = $this->evaluateFilters($filtered);

        if (is_null($count)) {
            if (!$evaluated) {
                $this->fail("No matching event [" . ValuePrinter::serialize($eventOrClass) . "] was recorded: " .
                    $this->printFilters($filtered));
            }
        } else if (count($evaluated) != $count) {
            $this->fail("Recorded [" . count($evaluated) . "] of [" . ValuePrinter::serialize($eventOrClass) . "] instead of [$count]: " .
                $this->printFilters($filtered));
        }
    }

    public function thenShouldNot($eventOrClass, callable $condition = null) {
        $filtered = $this->mapFilters($eventOrClass, $condition);
        $count = count($this->evaluateFilters($filtered));
        if ($count) {
            $this->fail(
                ($count == 1 ? "One event" : "$count events") .
                " [" . ValuePrinter::serialize($eventOrClass) . "] " .
                ($count == 1 ? "was" : "were") .
                " unexpectedly recorded: " .
                $this->printFilters($filtered));
        }
    }

    public function thenItShouldReturn($conditionOrValue) {
        if (is_callable($conditionOrValue)) {
            $conditions = $conditionOrValue($this->returned);
            if (is_array($conditions)) {
                foreach ($conditions as $name => $condition) {
                    if (is_array($condition)) {
                        if ($condition[0] != $condition[1]) {
                            $this->fail("The returned value does meet condition [$name]: " .
                                ValuePrinter::serialize($condition[0]) .
                                " should be " .
                                ValuePrinter::serialize($condition[1]));
                        }
                    } else if (!$condition) {
                        $this->fail("The returned value does meet condition [$name]");
                    }
                }
            } else if (!$conditions) {
                $this->fail('The returned value does not match the conditions.');
            }
        } else if ($conditionOrValue != $this->returned) {
            $this->fail("Returned value was [" . ValuePrinter::serialize($this->returned) . "] " .
                "instead of [" . ValuePrinter::serialize($conditionOrValue) . "]");
        }
    }

    public function thenItShouldFailWith($message, $exceptionClass = null) {
        if (!$this->caught) {
            $this->fail('No exception was thrown.');
        }

        if ($this->caught->getMessage() != $message) {
            $this->fail("Exception was [{$this->caught->getMessage()}] instead of [$message]");
        }

        if ($exceptionClass && !is_a($this->caught, $exceptionClass)) {
            $this->fail("Exception was of type [" . get_class($this->caught) . "] instead of [$exceptionClass]");
        }
    }

    protected function fail($message) {
        throw new \Exception($message);
    }

    private function mapFilters($eventOrClass, callable $condition = null) {
        return array_map(function ($event) use ($eventOrClass, $condition) {
            if (is_object($eventOrClass) && $event != $eventOrClass) {
                return false;
            } else if (is_string($eventOrClass) && !is_a($event, $eventOrClass)) {
                return false;
            } else if (!$condition) {
                return true;
            }

            return $condition($event);
        }, $this->events->allEvents());
    }

    private function evaluateFilters($filtered) {
        return array_filter($filtered, function ($filter) {
            if (!is_array($filter)) {
                return $filter;
            }

            foreach ($filter as $name => $condition) {
                if (is_array($condition) && $condition[0] != $condition[1]) {
                    return false;
                } else if (!$condition) {
                    return false;
                }
            }

            return true;
        });
    }

    private function printFilters($filtered) {
        $printed = [];
        foreach ($this->events->allEvents() as $i => $event) {
            if ($filtered[$i] === false) {
                $printed[$i] = '-';
            } else if (!is_array($filtered[$i])) {
                $printed[$i] = ValuePrinter::serialize($event);
            } else {
                $missed = [];
                foreach ($filtered[$i] as $name => $condition) {
                    if (is_array($condition) && $condition[0] != $condition[1]) {
                        $missed[$name] = [$condition[0], $condition[1]];
                    } else if (!$condition) {
                        $missed[$name] = $condition;
                    }
                }
                $printed[$i] = $missed;
            }
        }
        return ValuePrinter::serialize($printed);
    }
}