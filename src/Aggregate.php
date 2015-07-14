<?php

namespace appti2ude;
use appti2ude\inter\IDispatch;
use appti2ude\inter\IEvent;
use appti2ude\inter\IEventStore;
use appti2ude\inter\ISnapshot;

/**
 * Class Aggregate
 * @package appti2ude
 * @property int $eventsLoaded
 * @property string $id
 */
class Aggregate extends \appti2ude\bones\MagicClass {
	protected function AggregateInitialize() {
		$this->AddProperty('eventsLoaded', 0);
		$this->AddProperty('iApply', []);
		$this->AddProperty('iHandle', []);
		$this->AddProperty('dispatch', null);
	}

	function __construct(IDispatch $dispatcher = null, $id = null, $data = []) {
		parent::__construct($id, $data);
		$this->dispatch = $dispatcher;
	}

	protected function AddEventHandler($eventName, $funcName) {
		$applier = &$this->iApply;
		$applier[$eventName] = $funcName;
	}

	protected function addCommandHandler($command, $funcName) {
		$applier = &$this->iHandle;
		$applier[$command] = $funcName;
	}

	function test() {
		$this->id = 'id';
	}

	/**
	 * @param array $events
	 */
	public function ApplyEvents(array $events) {
		if (!empty($events)) {
			foreach ($events as $event) {
				$this->ApplyOneEvent($event);
			}
		}
	}

	public function ApplyOneEvent(IEvent $event) {
		if (isset($this->iApply[$event->type])) {
			$apply = $this->iApply[$event->type];
			$this->$apply($event);
			$this->eventsLoaded = $this->eventsLoaded + 1;
			return;
		}

		throw new \Exception('aggregate doesn\'t apply this event: ' . $event->type);
	}

	public function HydrateFromSnapshot(ISnapshot $snapshot) {
		$this->ApplyEvents($snapshot->GetEvents()); //todo: use snapshot
	}
}

