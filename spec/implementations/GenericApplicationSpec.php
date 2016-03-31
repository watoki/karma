<?php
namespace spec\watoki\karma\implementations;

use rtens\scrut\Assert;
use watoki\karma\command\AggregateFactory;
use watoki\karma\implementations\aggregates\CallbackAggregateFactory;
use watoki\karma\implementations\aggregates\CascadingAggregateFactory;
use watoki\karma\implementations\aggregates\GenericAggregateFactory;
use watoki\karma\implementations\aggregates\ObjectAggregateFactory;
use watoki\karma\implementations\GenericApplication;
use watoki\karma\implementations\projections\GenericProjection;
use watoki\karma\query\ProjectionFactory;
use watoki\karma\stores\MemoryEventStore;

/**
 * @property Assert assert <-
 */
class GenericApplicationSpec {

    function commandByDefault() {
        $this->application(
            GenericAggregateFactory::genericRoot(function ($command) {
                $this->assert->equals($command, 'foo');
            })
        )->handle('foo');
    }

    function commandCondition() {
        $application = $this->application()
            ->setCommandCondition(function ($commandOrQuery) {
                return $commandOrQuery == 'foo';
            });

        $this->assert->equals($application->handle('foo'), null);
        $this->assert->equals($application->handle('bar'), new GenericProjection());
    }

    function commandPattern() {
        $application = $this->application()
            ->setCommandPattern('/o/');

        $this->assert->equals($application->handle('foo'), null);
        $this->assert->equals($application->handle('bar'), new GenericProjection());
    }

    function commandClassNamePattern() {
        $application = $this->objectApplication()
            ->setCommandClassNamePattern('/DateTime/');

        $this->assert->equals($application->handle(new \DateTime()), null);
        $this->assert->equals($application->handle(new \StdClass()), new GenericProjection());
        $this->assert->equals($application->handle('foo'), new GenericProjection());
    }

    function commandBaseClass() {
        $application = $this->objectApplication()
            ->setCommandBaseClass(\DateTimeInterface::class);

        $this->assert->equals($application->handle(new \DateTime()), null);
        $this->assert->equals($application->handle(new \StdClass()), new GenericProjection());
        $this->assert->equals($application->handle('foo'), new GenericProjection());
    }

    function queryBaseClass() {
        $application = $this->objectApplication()
            ->setQueryBaseClass(\DateTimeInterface::class);

        $this->assert->equals($application->handle(new \DateTime()), new GenericProjection());
        $this->assert->equals($application->handle(new \StdClass()), null);
    }

    private function application(AggregateFactory $aggregates = null, ProjectionFactory $projections = null) {
        return new GenericApplication(new MemoryEventStore(), $aggregates, $projections);
    }

    private function objectApplication() {
        return $this->application(new CascadingAggregateFactory(
            (new CallbackAggregateFactory())->setHandleMethodCallback(function () {
                return 'handle';
            }),
            ObjectAggregateFactory::genericRoot()));
    }
}