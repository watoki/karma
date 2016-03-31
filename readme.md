# Karma [![Build Status](https://travis-ci.org/rtens/karma.png?branch=master)](https://travis-ci.org/rtens/karma)

*karma* is an [event sourcing] library for PHP. It helps you to

* handle commands
* store events
* react to events
* project events
* test your application

The name refers to the [principle of cause and effect][karma] where all actions of an entity influence
the future of that entity. Hence the state of the world is only the sum of all events
of the past. This is also the principle of [event sourcing].

>   Now as a man is like this or like that,<br>
    according as he acts and according as he behaves, so will he be;<br>
    a man of good acts will become good, a man of bad acts, bad;<br>
    he becomes pure by pure deeds, bad by bad deeds;<br><br>
    And here they say that a person consists of desires,<br>
    and as is his desire, so is his will;<br>
    and as is his will, so is his deed;<br>
    and whatever deed he does, that he will reap.<br><br>
    — Brihadaranyaka Upanishad, 7th Century BCE

[event sourcing]: http://martinfowler.com/eaaDev/EventSourcing.html
[karma]: https://en.wikipedia.org/wiki/Karma


## Installation ##

To use *karma* in your project, require it with [Composer]

    composer require rtens/karma
    
If you would like to develop on *karma*, clone it with [git], download its dependencies with [composer] and execute
the specification with [scrut].

    git clone https://github.com/watoki/karma.git
    cd karma
    composer install
    vendor/bin/scrut

[composer]: http://getcomposer.org/download/
[git]: https://git-scm.com/
[scrut]: https://github.com/rtens/scrut


## Usage ##

To just make sure that everything is piped together correctly you can do this

```php
(new GenericApplication(
    new MemoryEventStore()
))->handle('foo');
```

This simply appends processed commands to the event store

```php
(new GenericApplication(
    new StoringEventStore(new FileStore(__DIR__)),
    GenericAggregateFactory::genericRoot(function ($command) {
        return "handled $command";
    })
))->handle('foo');
```


## Documentation ##

The documentation of *karma* is written in the form of an executable specification. You find it in the [`spec`] folder.

[`spec`]: http://github.com/watoki/karma/tree/master/spec
