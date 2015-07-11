<?php

require_once 'lib/interfaces/ICommand.php';
require_once 'lib/interfaces/IDispatch.php';
require_once 'lib/interfaces/IEventStore.php';
require_once 'lib/interfaces/IEvent.php';
require_once 'lib/interfaces/ISnapshot.php';
require_once 'lib/MagicClass.php';
require_once 'lib/Aggregate.php';
require_once 'lib/Event.php';
require_once 'lib/MemoryEventStore.php';
require_once 'lib/MemoryDispatcher.php';

$store = new \appti2ude\MemoryEventStore();
$dispatch = new \appti2ude\MemoryDispatcher($store);

class AddEvent extends \appti2ude\Event {
}

class SubEvent extends \appti2ude\Event {
}

class Abicus extends \appti2ude\Aggregate {
	protected function AbicusInitialize() {
		$this->AddProperty('count', 0);
		$this->addEventHandler('AddEvent', 'AddOne');
		$this->addEventHandler('SubEvent', 'SubOne');
		$this->Debug($this->data);
	}

	function __construct($id, $data = []) {
		parent::__construct($id, $data);
	}

	function AddOne($event) {
		$this->count = $this->count + 1;
	}

	function SubOne($event) {
		$this->count = $this->count - 1;
	}
}

$ab = new Abicus('123');
$add = new AddEvent('123');
$sub = new SubEvent('123');

$ab->ApplyOneEvent($add);

echo "Added one, got: $ab->count";

/*header("content-type: application/json");

$test = new appti2ude\Aggregate();
$test->test();
$snap = json_decode($test->Snapshot());
$mine = appti2ude\bones\MagicClass::Hydrate($snap);
//echo $mine->snapshot();

class MiceEvent extends \appti2ude\Event {
}

class wonky extends \appti2ude\Aggregate {
	protected function wonkyInitialize() {
		$this->addEventHandler('MiceEvent', 'applyMice');
	}

	function applyMice(MiceEvent $donkey) {
		echo "yonk";
	}
}
$GLOBALS['appti2ude_mapper']->

$t = new wonky();
$t->ApplyOneEvent(new MiceEvent());
$t->ApplyOneEvent(new MiceEvent());
*/