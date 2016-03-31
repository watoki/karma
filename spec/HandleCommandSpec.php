<?php
namespace spec\watoki\karma;

use rtens\scrut\Assert;
use rtens\scrut\fixtures\ExceptionFixture;
use watoki\karma\implementations\aggregates\GenericAggregateRoot;
use watoki\karma\command\CommandHandler;
use watoki\karma\implementations\aggregates\GenericAggregateFactory;
use watoki\karma\implementations\listeners\CallbackListener;
use watoki\karma\stores\MemoryEventStore;

/**
 * @property Assert assert <-
 * @property ExceptionFixture try <-
 * @property MemoryEventStore store
 */
class HandleCommandSpec {

    function before() {
        $this->store = new MemoryEventStore();
    }

    function aggregateNotAnObject() {
        $this->try->tryTo(function () {
            $this->genericHandler('foo')->handle('bar');
        });
        $this->try->thenTheException_ShouldBeThrown('Aggregate root must be an object.');
    }

    function emptyAggregate() {
        $this->try->tryTo(function () {
            $this->genericHandler(new \StdClass())->handle('bar');
        });
        $this->try->thenTheException_ShouldBeThrown('Missing method stdClass::handle()');
    }

    function handleCommand() {
        $this->genericHandler(new GenericAggregateRoot(function ($command) {
            $this->assert->equals($command, 'foo');
        }))->handle('foo');
    }

    function appendSingleEvent() {
        $this->store->append('one', 'foo');

        $this->genericHandler(new GenericAggregateRoot(function () {
            return 'bar';
        }))->handle('foo');

        $this->assert->equals($this->store->allEvents(), ['one', 'bar']);
    }

    function appendMultipleEvents() {
        $this->store->append('one', 'foo');

        $this->genericHandler(new GenericAggregateRoot(function () {
            return ['bar', 'baz'];
        }))->handle('foo');

        $this->assert->equals($this->store->allEvents(), ['one', 'bar', 'baz']);
    }

    function applyEvents() {
        $this->store->append('one', 'foo');
        $this->store->append('two', 'bar');
        $this->store->append('three', 'foo');

        $this->genericHandler(new GenericAggregateRoot(function ($command, $events) {
            $this->assert->equals($command, 'foo');
            $this->assert->equals($events, ['one', 'three']);
        }))->handle('foo');
    }

    function invokeListeners() {
        $heard = [];
        $this->genericHandler(new GenericAggregateRoot(function () {
            return ['this', 'that'];
        }))
            ->addListener(new CallbackListener(function ($event) use (&$heard) {
                $heard[] = "heard $event";
            }))
            ->addListener(new CallbackListener(function ($event) use (&$heard) {
                $heard[] = "heard $event again";
            }))
            ->handle('foo');

        $this->assert->equals($heard, [
            'heard this',
            'heard that',
            'heard this again',
            'heard that again'
        ]);
    }

    function appendEventsBeforeInvokingListener() {
        $this->genericHandler(new GenericAggregateRoot(function () {
            return ['this', 'that'];
        }))
            ->addListener(new CallbackListener(function () {
                $this->assert->equals($this->store->allEvents(), ['this', 'that']);
            }))
            ->handle('foo');
    }

    function buildAggregateFromIdentifier() {
        $this->handler((new GenericAggregateFactory(function ($identifier) {
            $this->assert->equals($identifier, 'bar');
            return new GenericAggregateRoot(function () {
            });
        }))
            ->mapCommandToIdentifier('foo', 'bar'))
            ->handle('foo');
    }

    private function genericHandler($aggregateRoot) {
        return $this->handler(GenericAggregateFactory::staticRoot($aggregateRoot));
    }

    private function handler($aggregates) {
        return new CommandHandler($this->store, $aggregates);
    }
}