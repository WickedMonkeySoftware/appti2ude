<?php
namespace appti2ude;

use appti2ude\bones\MagicClass;
use appti2ude\inter\ICommand;
use appti2ude\inter\IDispatch;
use appti2ude\inter\IEvent;
use appti2ude\inter\IEventStore;

class MemoryDispatcher extends MagicClass implements IDispatch {
	private $eventStore;

	protected function MemoryDispatcherInitialize() {
		$this->AddProperty('commandHandlers', [], true);
		$this->AddProperty('eventSubscribers', [], true);
	}

	function __construct(IEventStore $eventStore, $id = null, $data = []) {
		parent::__construct($id, $data);
		$this->eventStore = $eventStore;
	}

	function SendCommand(ICommand $command) {
		if (isset($this->commandHandlers[$command->type])) {
			$handlers = $this->commandHandlers[$command->type];
			$callback = &$handlers;
			$type = $callback[0];
			$func = $callback[1];

			$data = $this->eventStore->LoadEventsFor($command->id);
			$t = new $type($this, $command->id, $data);

			foreach ($t->$func($command) as $event) {
				$this->PublishEvent($event);
			}
			unset($t);

			return;
		}

		throw new \Exception("No command handler registered: $command->type");
	}

	private function applyToSubscribers(IEvent $event, $eventType) {
		if (isset($this->eventSubscribers[$eventType])) {
			$subscribers = $this->eventSubscribers[$eventType];
			foreach ($subscribers as $callback) {
				$type = $callback[0];
				$func = $callback[1];

				$data = $this->eventStore->LoadEventsFor($event->id);
				$aggregate = new $type($this, $event->id);
				$aggregate->ApplyEvents($data);
				$aggregate->$func($event);
				$data[] = $event;

				$this->eventStore->SaveEventsFor($aggregate->id, $data);
				unset($aggregate);
			}
            return true;
		}
        return false;
	}

	function PublishEvent(IEvent $event) {
		$eventType = $event->type;
		if (!$this->applyToSubscribers($event, $eventType)) {
            // now we try publishing the same event in a less specific manner
            $eventType = end(explode('\\', $eventType));
            $this->applyToSubscribers($event, $eventType);
        }
	}

	function AddHandlerFor($command, $callback) {
		foreach ($callback as $call => $instance) {
			$type = get_class($instance);
			$handlers = &$this->commandHandlers;
			$handlers[$command] = [
				$type,
				$call
			];
		}
	}

	function AddSubscriberFor($event, $callback) {
		foreach ($callback as $call => $instance) {
			$type = get_class($instance);
			$subs = &$this->eventSubscribers;
			$subs[$event][] = [
				$type,
				$call
			];
		}
	}

	function Scan($instance) {
		$snap = Snapshot::TakeSnapshot($instance);
		$data = $snap->GetSnapshot();
		foreach ($data['iApply'] as $event => $func) {
			$this->AddSubscriberFor($event, [$func => $instance]);
		}
		foreach ($data['iHandle'] as $command => $func) {
			$this->AddHandlerFor($command, [$func => $instance]);
		}
	}
}
