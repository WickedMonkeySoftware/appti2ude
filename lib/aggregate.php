<?php

namespace appti2ude;

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
		$this->Debug($this->data);
	}

	protected function addEventHandler($eventName, $funcName) {
		$this->iApply[$eventName] = $funcName;
		$this->Debug($this->iApply);
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

	public function ApplyOneEvent(Event $event) {
		echo "umm";
		$this->Debug($this->iApply);
		if (isset($this->iApply[$event->type])) {
			$apply = $this->iApply[$event->type];
			$this->$apply($event);
			$this->eventsLoaded = $this->eventsLoaded + 1;
			return;
		}

		throw new \Exception('aggregate doesn\'t apply this event: ' . $event->type);
	}
}

