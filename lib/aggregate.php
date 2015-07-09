<?php

namespace appti2ude;

/**
 * Class Aggregate
 * @package appti2ude
 * @property int $eventsLoaded
 * @property string $id
 */
class Aggregate extends \appti2ude\bones\MagicClass {
	protected $properties = [
		'public' => [
			'eventsLoaded' => ['public', 'private'],
			'id' => ['public', 'private'],
		]
	];

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

	}
}