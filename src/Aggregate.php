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
        $this->AddProperty('iNeed', []);
        $this->AddProperty('iBlacklist', []);
		$this->AddProperty('dispatch', null);
	}

	function __construct(IDispatch $dispatcher = null, $id = null, $data = []) {
		parent::__construct($id, $data);
		$this->dispatch = $dispatcher;
	}

	protected function AddEventHandler($eventName, $funcName, $require = null, $blacklist = null) {
		$applier = &$this->iApply;
		$applier[$eventName] = $funcName;

        if ($require != null) {
            $this->iNeed[$eventName] = $require;
        }

        if ($blacklist != null) {
            $this->iBlacklist[$eventName] = $blacklist;
        }
	}

	protected function AddCommandHandler($command, $funcName) {
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

	private function doApply($event, $type) {
		if (isset($this->iApply[$type])) {
			$apply = $this->iApply[$type];
			$this->$apply($event);
			$this->eventsLoaded = $this->eventsLoaded + 1;
			return true;
		}

		return false;
	}

	public function ApplyOneEvent(IEvent $event) {
		$type = $event->type;
		$applied = $this->doApply($event, $type);

		if (!$applied) {
			$type = end(explode('\\', $type));
			$applied = $this->doApply($event, $type);
		}

		if (!$applied)
			throw new \Exception('aggregate doesn\'t apply this event: ' . $event->type);
	}

	public function HydrateFromSnapshot(ISnapshot $snapshot) {
		$this->ApplyEvents($snapshot->GetEvents()); //todo: use snapshot
	}
}

