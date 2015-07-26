<?php

require_once 'src/inter/ICommand.php';
require_once 'src/inter/IDispatch.php';
require_once 'src/inter/IEventStore.php';
require_once 'src/inter/IEvent.php';
require_once 'src/inter/ISnapshot.php';
require_once 'src/MagicClass.php';
require_once 'src/Aggregate.php';
require_once 'src/Event.php';
require_once 'src/Command.php';
require_once 'src/Snapshot.php';
require_once 'src/MemoryEventStore.php';
require_once 'src/MemoryDispatcher.php';

$store = new \appti2ude\MemoryEventStore();
$dispatch = new \appti2ude\MemoryDispatcher($store);

class AddEvent extends \appti2ude\Event {
}

class SubEvent extends \appti2ude\Event {
}

class AddCommand extends \appti2ude\Command {
	protected function AddCommandInitialize() {
		$this->AddProperty('amount', 0);
	}
}

class SubtractCommand extends AddCommand {

}

class Abicus extends \appti2ude\Aggregate {
	protected function AbicusInitialize() {
		$this->AddProperty('count', 0);
		$this->AddEventHandler('AddEvent', 'AddOne');
		$this->AddEventHandler('SubEvent', 'SubOne');
		$this->addCommandHandler('AddCommand', 'Add');
		$this->addCommandHandler('SubtractCommand', 'Subtract');
	}

	function Add($command) {
		for($i = 0; $i < $command->amount; $i++) {
			$event = new AddEvent($this->id);
			yield $event;
		}
	}

	function Subtract($command) {
		for($i = 0; $i < $command->amount; $i++) {
			yield new SubEvent($this->id);
		}
	}

	function AddOne($event) {
		$this->count = $this->count + 1;
	}

	function SubOne($event) {
		$this->count = $this->count - 1;
	}
}

//$ab = new Abicus(null, '123');
//$add = new AddEvent('123');
//$sub = new SubEvent('123');

//$ab->ApplyOneEvent($add);

//echo "Added one, got: $ab->count";

//$ab->ApplyOneEvent($sub);

//echo "Sub one, got: $ab->count";

//echo "<h1>Testing dispatch</h1>";


$add1 = new AddCommand('123', ['amount' => 1]);
$add10 = new AddCommand('123', ['amount' => 10]);
$sub1 = new SubtractCommand('123', ['amount' => 1]);

$store = new \appti2ude\MemoryEventStore();
$dispatch = new \appti2ude\MemoryDispatcher($store, null);
$abicus = new Abicus($dispatch, '123');
$dispatch->Scan($abicus);
$dispatch->SendCommand($add1);

// verify
$ab = new Abicus(null, '123');
$snapshot = \appti2ude\Snapshot::CreateFromStore($store, '123');
$ab->HydrateFromSnapshot($snapshot);

header('content-type: application/json');
echo json_encode($ab->Snapshot());

//echo "Got: $ab->count";

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
