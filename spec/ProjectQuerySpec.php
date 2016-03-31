<?php
namespace spec\watoki\karma;

use rtens\scrut\Assert;
use rtens\scrut\fixtures\ExceptionFixture;
use watoki\karma\implementations\projections\GenericProjectionFactory;
use watoki\karma\implementations\projections\GenericProjection;
use watoki\karma\query\QueryProjector;
use watoki\karma\stores\MemoryEventStore;

/**
 * @property ExceptionFixture try <-
 * @property Assert assert <-
 * @property MemoryEventStore store
 */
class ProjectQuerySpec {

    function before() {
        $this->store = new MemoryEventStore();
    }

    function notAnObject() {
        $this->try->tryTo(function () {
            $this->genericProjector('foo')->project('foo');
        });
        $this->try->thenTheException_ShouldBeThrown('Projection must be an object.');
    }

    function emptyProjection() {
        $projection = $this->genericProjector(new \StdClass())->project();
        $this->assert->equals($projection, new \StdClass());
    }

    function applyEvents() {
        $this->store->append('one', 'foo');
        $this->store->append('two', 'bar');
        $this->store->append('three', 'foo');
        $this->store->append('four', 'bar');

        $projection = $this->genericProjector(new GenericProjection())->project();
        $this->assert->equals($projection->getEvents(), ['one', 'three', 'two', 'four']);
    }

    function buildProjection() {
        $this->projector(new GenericProjectionFactory(function ($query) {
            $this->assert->equals($query, 'foo');
            return new \StdClass();
        }))->project('foo');
    }

    private function genericProjector($projection) {
        return $this->projector(GenericProjectionFactory::staticProjection($projection));
    }

    private function projector($projections) {
        return new QueryProjector($this->store, $projections);
    }
}