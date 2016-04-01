# Karma [![Build Status](https://travis-ci.org/watoki/karma.png?branch=master)](https://travis-ci.org/watoki/karma)

*karma* is an [event sourcing] library for PHP. It helps you to

* handle commands
* store events
* protect invariants
* react to events
* project events
* test an application

The name refers to the [principle of cause and effect][karma] where all actions of an entity influence
the future of that entity. Hence the state of the world is only the sum of all events
of the past. This is also the principle of [event sourcing].

> Shallow men believe in luck or in circumstance. Strong men believe in cause and effect.<br>
Ralph Waldo Emerson

[event sourcing]: http://martinfowler.com/eaaDev/EventSourcing.html
[karma]: https://en.wikipedia.org/wiki/Karma


## Installation

To use *karma* in your project, require it with [Composer]

    composer require watoki/karma
    
If you would like to develop on *karma*, clone it with [git], download its dependencies with [composer] and execute
the specification with [scrut].

    git clone https://github.com/watoki/karma.git
    cd karma
    composer install
    vendor/bin/scrut

[composer]: http://getcomposer.org/download/
[git]: https://git-scm.com/
[scrut]: https://github.com/rtens/scrut


## Usage

```php
$store = new MemoryEventStore();
$application = new CommandQueryApplication($store);
```

### Handle Commands

```php
class MyAggregate {

    public function handleFoo(Foo $foo) {
        echo "Handled " . $foo->what;
    }
}
```

```php
$application->handle(new Foo('this'));
```

```php
class Foo extends MyCommand {

    public function __construct($what) {
        $this->what = $what;
    }
}
```

```php
class MyCommand implements Command {

    public function getAggregateIdentifier() {
        return 'foo';
    }

    public function getAggregateRoot() {
        return new MyAggregate();
    }
}
```

### Store Events

```php
class Fooed {

    public function __construct($how) {
        $this->how = $how;
    }
}
```

```php
class MyAggregate {

    public function handleFoo(Foo $foo) {
        return new Fooed($foo->what . ' happened');
    }
}
```

```php
$application->handle(new Foo('this'));
var_dump($store->allEvents());
```

### React to Events

```php
class MyListener {

    public function onFooed(Fooed $fooed) {
        echo "Looks like " . $fooed->what;
    }
}
```

```php
$application->addListener(new StaticListener(new MyListener()));
$application->handle(new Foo('this'));
```

### Protect Invariants

```php
class MyAggregate {

    private $fooed = false;

    public function applyFooed() {
        $this->fooed = true;
    }

    public function handleFoo(Foo $foo) {
        if ($this->fooed) {
            throw new \Exception('Foo me once shame on you, foo me twice shame on me');
        }
        return new Fooed($foo->that . ' happened');
    }
}
```

### Project Events

```php
class MyProjection {

    public $fooed = [];

    public function applyFooed(Fooed $fooed) {
        $this->fooed[] = 'So ' . $fooed->what;
    }
}
```

```php
$bar = $application->handle(new Bar());
echo implode(PHP_EOL, $bar->fooed);
```

```php
class Bar implements Query {

    public function getProjection() {
        return new MyProjection();
    }
}
```


## Documentation

The documentation of *karma* is written in the form of an executable specification. You find it in the [`spec`] folder.

[`spec`]: http://github.com/watoki/karma/tree/master/spec
