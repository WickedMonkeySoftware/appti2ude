<?php

namespace appti2ude;

use appti2ude\inter\ISnapshot;

class Snapshot implements ISnapshot {
	private $snapshot = [];

	function __construct($snap) {
		$this->snapshot = $snap;
	}

	public static function Serialize(Aggregate $aggregate) : Aggregate {
		$state = $aggregate->Snapshot();
		$snap = [];
		foreach ($state['events'] as $event) {
			$snap['events'][] = $event->Snapshot();
		}
		$snap['state'] = $state['lastSnapshot'];
		return new Snapshot($snap);
	}

	public static function Create(array $data = [], array $events = []) {
		$snapshot = [];
		foreach ($events as $event) {
			$snapshot['events'][] = $event->Snapshot();
		}

		$snapshot['state'] = $data['lastSnapshot'];
		return new Snapshot($snapshot);
	}

	public static function TakeSnapshot(Aggregate $aggregate) : Snapshot {
		$state = $aggregate->Snapshot();
		$state['lastSnapshot'] = $state;
		$state = [
			'events' => [],
			'state' => $state
		];
		return new Snapshot($state);
	}

	public function GetSnapshot() {
		return $this->snapshot['state'];
	}

	public function GetEvents() {
		return $this->snapshot['events'];
	}
}