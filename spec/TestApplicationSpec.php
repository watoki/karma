<?php
namespace spec\watoki\karma;

use rtens\scrut\Assert;
use rtens\scrut\fixtures\ExceptionFixture;
use watoki\karma\command\AggregateFactory;
use watoki\karma\implementations\aggregates\GenericAggregateFactory as Aggregate;
use watoki\karma\implementations\GenericApplication;
use watoki\karma\implementations\listeners\CallbackListener;
use watoki\karma\implementations\projections\GenericProjection;
use watoki\karma\stores\EventStore;
use watoki\karma\testing\Specification;

/**
 * @property Specification specification
 * @property ExceptionFixture try <-
 * @property Assert assert <-
 */
class TestApplicationSpec {

    function noEventStored() {
        $this->try->tryTo(function () {
            $this->command()->then('foo');
        });
        $this->try->thenTheException_ShouldBeThrown('Event was not appended');
    }

    function findEvent() {
        $spec = $this->command(Aggregate::genericRoot(function ($command) {
            return $command . 'd';
        }));

        $spec->when('foo');
        $spec->then('food');
        $spec->then->should('food')->count(1);

        $this->assert->contains($spec->appendedEvents(), 'food');
    }

    function expectedException() {
        $spec = $this->command(Aggregate::genericRoot(function () {
            throw new \Exception('Boom');
        }));

        $spec->when->tryTo('foo');

        $spec->then->shouldFail();
        $spec->then->shouldFail('Boom');

        $this->try->tryTo(function () use ($spec) {
            $spec->then->shouldFail('Not');
        });
        $this->try->thenTheException_ShouldBeThrown('Exception [Boom] should be [Not]');
    }

    function noException() {
        $spec = $this->command(Aggregate::genericRoot(function ($command) {
            if ($command == 'foo') {
                throw new \Exception('Boom');
            }
        }));

        $spec->when->tryTo('foo');
        $spec->when->tryTo('bar');

        $this->try->tryTo(function () use ($spec) {
            $spec->then->shouldFail();
        });
        $this->try->thenTheException_ShouldBeThrown('No exception was thrown.');
    }

    function wrongExceptionMessage() {
        $spec = $this->command(Aggregate::genericRoot(function () {
            throw new \Exception('Boom');
        }));

        $spec->when->tryTo('foo');

        $this->try->tryTo(function () use ($spec) {
            $spec->then->shouldFail('Not');
        });
        $this->try->thenTheException_ShouldBeThrown('Exception [Boom] should be [Not]');
    }

    function createContext() {
        $spec = $this->command(Aggregate::genericRoot(function ($command, $events) {
            return $command . ',' . implode(',', $events);
        }));

        $spec->given('food');
        $spec->given('bazd', 'other');
        $spec->given('bard');

        $spec->when('baz');
        $spec->then('baz,food,bard');

        $this->assert->equals($spec->appendedEvents(), [
            'food',
            'bard',
            'baz,food,bard',
            'bazd'
        ]);
    }

    function invokeListeners() {
        $heard = [];

        $listenToAll = new CallbackListener(function ($event) use (&$heard) {
            $heard[] = $event;
        });

        $listensToAllButBar = new CallbackListener(function ($event) use (&$heard) {
            $heard[] = $event . ' again';
        }, function ($event) {
            return $event != 'bard';
        });

        $aggregate = Aggregate::genericRoot(function ($command) {
            return $command . 'd';
        });

        $spec = $this->command($aggregate, [$listenToAll, $listensToAllButBar]);

        $spec->given('food');
        $spec->given('bard');
        $spec->when('baz');

        $this->assert->equals($heard, [
            'food',
            'food again',
            'bard',
            'bazd',
            'bazd again'
        ]);
    }

    function compareEvents() {
        $spec = $this->command();

        $spec->given('foo');

        $spec->then->shouldMatch(function ($event) {
            return $event == 'foo';
        })->count(1);

        $spec->then->not->shouldMatch(function ($event) {
            return $event == 'bar';
        });

        $this->assert->pass();
    }

    function compareComplexEvents() {
        $spec = $this->command();

        $spec->given('foo');
        $spec->given([
            'one' => 'foo',
            'two' => 'bar'
        ]);

        $conditions = function ($event) {
            return is_array($event) ? [
                'one' => [$event['one'], 'foo'],
                'two' => [$event['two'], 'foo'],
                'three' => isset($event['three'])
            ] : [
                'array' => false
            ];
        };

        $this->try->tryTo(function () use ($spec, $conditions) {
            $spec->then->shouldMatchAll($conditions);
        });
        $this->try->thenTheException_ShouldBeThrown(
            'No matching event found: [{"array":false},{"two":"bar","three":false}]');

        $spec->given([
            'one' => 'foo',
            'two' => 'foo',
            'three' => 'foo'
        ]);

        $spec->then->shouldMatchAll($conditions)->count(1);
    }

    function compareEventObjects() {
        $spec = $this->command();

        $spec->given('foo');
        $spec->given(new \DateTimeImmutable('2011-12-13'));
        $spec->given(new \DateTime('2011-12-13'));
        $spec->given(new \DateTime('2011-12-14'));

        $spec->then->not->shouldMatchClass(\DateTimeInterface::class);

        $spec->then->shouldMatchClass(\DateTime::class)->count(2);

        $spec->then->shouldMatchObject(\DateTime::class, function (\DateTime $dateTime) {
            return $dateTime > new \DateTime('2011-12-13');
        })->count(1);

        $spec->then->shouldMatchAllObject(\DateTime::class, function (\DateTime $dateTime) {
            return [
                'time' => [$dateTime->format('Y-m-d'), '2011-12-13']
            ];
        })->count(1);

        $this->assert->pass();
    }

    function nothingReturned() {
        $spec = $this->projection();

        $spec->when('foo');

        $this->try->tryTo(function () use ($spec) {
            $spec->then->shouldReturn('foo');
        });
        $this->try->thenTheException_ShouldBeThrown('Unexpected return value');
    }

    function expectedProjection() {
        $spec = $this->projection();

        $spec->when('project');
        $spec->then->shouldReturn(new GenericProjection());
    }

    private function command(AggregateFactory $aggregate = null, array $listeners = []) {
        return new Specification(function (EventStore $store) use ($aggregate) {
            return new GenericApplication($store, $aggregate);
        }, $listeners);
    }

    private function projection() {
        return new Specification(function (EventStore $store) {
            return (new GenericApplication($store))->setCommandPattern('/project/');
        });
    }
}