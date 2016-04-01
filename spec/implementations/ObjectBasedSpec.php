<?php
namespace spec\watoki\karma\implementations;

use rtens\scrut\Assert;
use watoki\karma\implementations\aggregates\ObjectAggregateFactory;
use watoki\karma\implementations\GenericApplication;
use watoki\karma\implementations\listeners\ObjectListener;
use watoki\karma\implementations\listeners\StaticListener;
use watoki\karma\implementations\projections\ObjectProjectionFactory;
use watoki\karma\stores\MemoryEventStore;

/**
 * @property Assert assert <-
 * @property array heard
 * @property MemoryEventStore store
 */
class ObjectBasedSpec {

    function before() {
        $this->store = new MemoryEventStore();
        $this->heard = [];
        ObjectBasedSpec_FooAggregate::$applied = [];
        ObjectBasedSpec_FooAggregate::$handled = [];
        ObjectBasedSpec_BarAggregate::$handled = [];
        ObjectBasedSpec_FooProjection::$applied = [];
        ObjectBasedSpec_BarProjection::$applied = [];
    }

    function handleCommand() {
        $this->application()
            ->addListener(new ObjectListener(function (ObjectBasedSpec_FooEvent $event) {
                $this->heard[] = $event;
            }, ObjectBasedSpec_FooEvent::class))
            ->handle(new ObjectBasedSpec_FooCommand());

        $this->assert->equals(ObjectBasedSpec_FooAggregate::$handled, [new ObjectBasedSpec_FooCommand()]);
        $this->assert->equals($this->heard, [new ObjectBasedSpec_FooEvent(new ObjectBasedSpec_FooCommand())]);
    }

    function applyEvents() {
        $this->store->append(new ObjectBasedSpec_FooEvent(), 'karma');
        $this->store->append(new ObjectBasedSpec_BarEvent(), 'karma');

        $this->application()
            ->handle(new ObjectBasedSpec_FooCommand());

        $this->assert->equals(ObjectBasedSpec_FooAggregate::$applied, [
            new ObjectBasedSpec_FooEvent(),
            new ObjectBasedSpec_BarEvent()
        ]);
    }

    function mapIdentifierToRoot() {
        $application = (new GenericApplication(
            $this->store,
            ObjectAggregateFactory::mappedRoot([
                ObjectBasedSpec_FooCommand::class => new ObjectBasedSpec_FooAggregate(),
                ObjectBasedSpec_BarCommand::class => new ObjectBasedSpec_BarAggregate()
            ])
        ));

        $application->handle(new ObjectBasedSpec_FooCommand());
        $application->handle(new ObjectBasedSpec_BarCommand());

        $this->assert->equals(ObjectBasedSpec_FooAggregate::$handled, [new ObjectBasedSpec_FooCommand()]);
        $this->assert->equals(ObjectBasedSpec_BarAggregate::$handled, [new ObjectBasedSpec_BarCommand()]);
    }

    function doNotListenToBaseClass() {
        $this->application()
            ->addListener(new ObjectListener(function (ObjectBasedSpec_FooEvent $event) {
                $this->heard[] = $event;
            }, ObjectBasedSpec_FooEvent::class))
            ->addListener(new ObjectListener(function (ObjectBasedSpec_BarEvent $event) {
                $this->heard[] = $event;
            }, ObjectBasedSpec_BarEvent::class))
            ->handle(new ObjectBasedSpec_BarCommand());

        $this->assert->equals($this->heard, [
            new ObjectBasedSpec_BarEvent(new ObjectBasedSpec_BarCommand())
        ]);
    }

    function staticListener() {
        $listener = new ObjectBasedSpec_Listener();
        $application = $this->application()
            ->addListener($listener);

        $application->handle(new ObjectBasedSpec_FooCommand());
        $application->handle(new ObjectBasedSpec_BarCommand());

        $this->assert->equals($listener->heard, [new ObjectBasedSpec_FooEvent(new ObjectBasedSpec_FooCommand())]);
    }

    function delegateListener() {
        $listener = new ObjectBasedSpec_DelegateListener();
        $application = $this->application()
            ->addListener(new ObjectBasedSpec_Listener($listener));

        $application->handle(new ObjectBasedSpec_FooCommand());

        $this->assert->equals($listener->heard, [new ObjectBasedSpec_FooEvent(new ObjectBasedSpec_FooCommand())]);
    }

    function projectQuery() {
        $this->store->append(new ObjectBasedSpec_FooEvent(), 'foo');
        $this->store->append(new ObjectBasedSpec_BarEvent(), 'bar');

        $this->application()
            ->setCommandClassNamePattern('/Command$/')
            ->handle(new ObjectBasedSpec_FooQuery());

        $this->assert->equals(ObjectBasedSpec_FooProjection::$applied, [
            new ObjectBasedSpec_FooEvent(),
            new ObjectBasedSpec_BarEvent()
        ]);
    }

    function mapQueryToProjection() {
        $this->store->append(new ObjectBasedSpec_FooEvent(), 'foo');
        $this->store->append(new ObjectBasedSpec_BarEvent(), 'bar');

        $application = (new GenericApplication(
            $this->store,
            ObjectAggregateFactory::staticRoot(new ObjectBasedSpec_FooAggregate()),
            ObjectProjectionFactory::mappedProjection([
                ObjectBasedSpec_FooQuery::class => new ObjectBasedSpec_FooProjection(),
                ObjectBasedSpec_BarQuery::class => new ObjectBasedSpec_BarProjection(),
            ])
        ))->setCommandClassNamePattern('/Command$/');

        $application->handle(new ObjectBasedSpec_FooQuery());
        $application->handle(new ObjectBasedSpec_BarQuery());

        $this->assert->equals(ObjectBasedSpec_FooProjection::$applied, [new ObjectBasedSpec_FooEvent(), new ObjectBasedSpec_BarEvent()]);
        $this->assert->equals(ObjectBasedSpec_BarProjection::$applied, [new ObjectBasedSpec_BarEvent()]);
    }

    private function application() {
        return (new GenericApplication(
            $this->store,
            ObjectAggregateFactory::staticRoot(new ObjectBasedSpec_FooAggregate()),
            ObjectProjectionFactory::staticProjection(new ObjectBasedSpec_FooProjection())
        ));
    }
}

class ObjectBasedSpec_FooAggregate {
    public static $applied;
    public static $handled;

    public function handleObjectBasedSpec_FooCommand(ObjectBasedSpec_FooCommand $command) {
        self::$handled[] = $command;
        return new ObjectBasedSpec_FooEvent($command);
    }

    public function handleObjectBasedSpec_BarCommand(ObjectBasedSpec_BarCommand $command) {
        self::$handled[] = $command;
        return new ObjectBasedSpec_BarEvent($command);
    }

    public function applyObjectBasedSpec_BarEvent(ObjectBasedSpec_BarEvent $event) {
        self::$applied[] = $event;
    }

    public function applyObjectBasedSpec_FooEvent(ObjectBasedSpec_FooEvent $event) {
        self::$applied[] = $event;
    }
}

class ObjectBasedSpec_BarAggregate {
    public static $handled;

    public function handleObjectBasedSpec_BarCommand(ObjectBasedSpec_BarCommand $command) {
        self::$handled[] = $command;
        return new ObjectBasedSpec_BarEvent($command);
    }
}

class ObjectBasedSpec_FooProjection {
    public static $applied;

    public function applyObjectBasedSpec_FooEvent(ObjectBasedSpec_FooEvent $event) {
        self::$applied[] = $event;
    }

    public function applyObjectBasedSpec_BarEvent(ObjectBasedSpec_BarEvent $event) {
        self::$applied[] = $event;
    }
}


class ObjectBasedSpec_BarProjection {
    public static $applied;

    public function applyObjectBasedSpec_BarEvent(ObjectBasedSpec_BarEvent $event) {
        self::$applied[] = $event;
    }
}

class ObjectBasedSpec_FooEvent {
    public function __construct($command = null) {
        $this->command = $command;
    }
}

class ObjectBasedSpec_BarEvent extends ObjectBasedSpec_FooEvent {
}

class ObjectBasedSpec_FooCommand {
}

class ObjectBasedSpec_BarCommand {
}

class ObjectBasedSpec_FooQuery {
}

class ObjectBasedSpec_BarQuery {
}

class ObjectBasedSpec_Listener extends StaticListener {
    public $heard = [];

    public function onObjectBasedSpec_FooEvent(ObjectBasedSpec_FooEvent $event) {
        $this->heard[] = $event;
    }
}

class ObjectBasedSpec_DelegateListener {
    public $heard = [];

    public function onObjectBasedSpec_FooEvent(ObjectBasedSpec_FooEvent $event) {
        $this->heard[] = $event;
    }
}