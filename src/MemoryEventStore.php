<?php
namespace appti2ude;

use appti2ude\bones\MagicClass;
use appti2ude\inter\IEvent;
use appti2ude\inter\IEventStore;

class MemoryEventStore extends MagicClass implements IEventStore {
	protected function MemoryEventStoreInitialize() {
		$this->AddProperty('store', []);
	}

	function __construct() {
		parent::__construct();
	}

	public function LoadEventsFor($id) : array {
		return $this->store[$id] ?? [];
	}

	public function SaveEventsFor($id, array $events) {
		$store = &$this->store;

		//todo: a rather nieve implementation -- should do proper versioning, and doesn't support snapshots
		if (count($store[$id]) != count($events)) {
			$store[$id] = $events;
		}
	}
}