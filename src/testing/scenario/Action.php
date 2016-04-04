<?php
namespace watoki\karma\testing\scenario;

use watoki\karma\Application;

class Action {

    /** @var Application */
    private $application;
    /** @var Outcome */
    private $outcome;

    /**
     * @param Application $application
     * @param Outcome $outcome
     */
    public function __construct(Application $application, Outcome $outcome) {
        $this->application = $application;
        $this->outcome = $outcome;
    }

    public function __invoke($commandOrQuery) {
        $this->outcome->reset();
        $this->outcome->returned = $this->application->handle($commandOrQuery);
    }

    public function tryTo($commandOrQuery) {
        try {
            $this->__invoke($commandOrQuery);
        } catch (\Exception $e) {
            $this->outcome->failed($e);
        }
    }
}