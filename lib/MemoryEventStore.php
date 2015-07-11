<?php
namespace appti2ude;

use appti2ude\bones\MagicClass;
use appti2ude\inter\IEventStore;

class MemoryEventStore extends MagicClass implements IEventStore {
	protected function MemoryEventStoreInitialize() {
		$this->AddProperty('store', []);
	}

	function __construct() {
		parent::__construct();
	}

	public function LoadEventsFor($id, $aggregate) : array {
		return $this->store[$aggregate][$id] ?? [];
	}

	public function SaveEventsFor($id, Aggregate $aggregate) {
		$save = Snapshot::Serialize($aggregate);

		//todo: a rather nieve implementation -- should do proper versioning, and doesn't support snapshots
		if (count($this->store[$aggregate->Name()][$aggregate->id]) != count($save['events'])) {
			$store[$aggregate->Name()][$aggregate->id] = clone $save['events'];
		}
	}
}