<?php
/**
 * Created by PhpStorm.
 * User: rlanders
 * Date: 7/13/15
 * Time: 1:17 PM
 */

namespace appti2ude;

require_once './src/inter/IEvent.php';
require_once './src/MagicClass.php';
require_once './src/Aggregate.php';
require_once './src/Event.php';

class TestEvent extends Event {
	protected function TestEventInitialize() {
		$this->AddProperty('myEvent', true);
	}
}

class TestAgg extends Aggregate {
	protected function TestAggInitialize() {
		$this->AddProperty('lastEvent', null);
		$this->AddEventHandler('appti2ude\TestEvent', 'ApplyTestEvent');
	}

	public function ApplyTestEvent($event) {
		$this->lastEvent = $event->myEvent;
	}
}

class AggregateTest extends \PHPUnit_Framework_TestCase {
	function testCreation() {
		$t = new TestAgg();
		$this->assertInstanceOf('appti2ude\TestAgg', $t);
	}

	function testApplyEvents() {
		$t = new TestAgg(null, '123');
		$e1 = new TestEvent('123', ['myEvent' => 'lastone', 'version' => 1]);
		$e2 = new TestEvent('123', ['myEvent' => 'updated', 'version' => 2]);
		$es = [$e2];
		$this->assertEmpty($t->lastEvent);
		$v = $t->Snapshot();
		$this->assertArrayHasKey('iApply', $v);
		$t->ApplyOneEvent($e1);
		$this->assertEquals('lastone', $t->lastEvent);
		$t->ApplyEvents($es);
		$this->assertEquals('updated', $t->lastEvent);
	}
}
