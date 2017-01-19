<?php
namespace spec\watoki\karma\stores;

use rtens\scrut\Assert;
use watoki\karma\stores\CachingEventStore;
use watoki\karma\stores\EventStore;
use watoki\karma\stores\StoringEventStore;
use watoki\stores\Store;

/**
 * @property SpyStore $spy <-
 * @property Assert $assert <-
 * @property EventStore $store
 */
class CacheEventsSpec {

    function before() {
        $this->store = new CachingEventStore(new StoringEventStore($this->spy));
    }

    function readAll() {
        $this->store->allEvents();
        $this->store->allEvents();

        $this->assert->equals($this->spy->log, [
            ['keys'],
            ['read', 'foo'],
            ['read', 'bar']
        ]);
    }

    function readOne() {
        $this->store->eventsOf('foo');
        $this->store->eventsOf('foo');

        $this->assert->equals($this->spy->log, [
            ['has', 'foo'],
            ['read', 'foo'],
        ]);
    }

    function readNotExisting() {
        $this->store->eventsOf('not');
        $this->store->eventsOf('not');

        $this->assert->equals($this->spy->log, [
            ['has', 'not'],
        ]);
    }

    function appendToCache() {
        $this->store->eventsOf('foo');
        $this->store->append(0, 'foo');

        $this->assert->equals($this->spy->log, [
            ['has', 'foo'],
            ['read', 'foo'],
            ['write', 'foo', [1, 2, 3, 0]]
        ]);
    }
}

class SpyStore implements Store {

    public $log = [];
    public $data = [
        'foo' => [1, 2, 3],
        'bar' => [4, 5, 6]
    ];

    public function write($data, $key = null) {
        $this->log[] = ['write', $key, $data];
        $this->data[$key] = $data;
    }

    public function read($key) {
        $this->log[] = ['read', $key];
        return $this->data[$key];
    }

    public function remove($key) {
        $this->log[] = ['remove', $key];
        unset($this->data[$key]);
    }

    public function has($key) {
        $this->log[] = ['has', $key];
        return array_key_exists($key, $this->data);
    }

    public function keys() {
        $this->log[] = ['keys'];
        return array_keys($this->data);
    }
}