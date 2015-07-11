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
			$callback = $handlers;
			$type = $callback[0];
			$func = $callback[1];

			$data = $this->eventStore->LoadEventsFor($command->id);
			$t = new $type($command->id, $data);
			foreach ($t->$func($command) as $event) {
				$this->PublishEvent($event);
			}
		}

		throw new \Exception('No command handler registered');
	}

	function PublishEvent(IEvent $event) {
		$eventType = $event->type;
		if (isset($this->eventSubscribers[$eventType])) {
			$subscribers = $this->eventSubscribers[$eventType];
			foreach ($subscribers as $callback) {
				$type = $callback[0];
				$func = $callback[1];

				$data = $this->eventStore->LoadEventsFor($event->id);
				$aggregate = new $type($event->id, $data);
				$aggregate->$func($event);

				$this->eventStore->SaveEventsFor($aggregate->id, $aggregate);
			}
		}
	}

	function AddHandlerFor($command, array $callback) {
		$type = get_class($callback[0]);
		if (isset($this->commandHandlers[$command])) {
			throw new \Exception('Command handler already defined');
		}

		$this->commandHandlers[$command] = [
			$type,
			$callback[1]
		];
	}

	function AddSubscriberFor($event, array $callback) {
		$type = get_class($callback[0]);
		$this->eventSubscribers[$event][] = [
			$type,
			$callback[1]
		];
	}

	function Scan($instance) {
		$snap = Snapshot::TakeSnapshot($instance);
		$data = $snap->GetSnapshot();
		foreach ($data['iApply'] as $event => $func) {
			$this->AddSubscriberFor($event, [$instance => $func]);
		}
		foreach ($data['iHandle'] as $command => $func) {
			$this->AddHandlerFor($command, [$instance => $func]);
		}
	}
}