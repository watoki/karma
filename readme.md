# Karma [![Build Status](https://travis-ci.org/watoki/karma.png?branch=master)](https://travis-ci.org/watoki/karma)

*karma* is an [event sourcing] library for PHP. It helps you to

* [handle commands](http://github.com/watoki/karma/tree/master/spec/usage/HandleCommandsSpec.php)
* [store events](http://github.com/watoki/karma/tree/master/spec/usage/StoreEventsSpec.php)
* [protect invariants](http://github.com/watoki/karma/tree/master/spec/usage/ProtectInvariantsSpec.php)
* [react to events](http://github.com/watoki/karma/tree/master/spec/usage/ReactToEventsSpec.php)
* [project events](http://github.com/watoki/karma/tree/master/spec/usage/ProjectEventsSpec.php)
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


## Documentation

The documentation of *karma* is written in the form of an executable specification. You find it in the [`spec`] folder.

[`spec`]: http://github.com/watoki/karma/tree/master/spec
