<?php
/**
 * Created by PhpStorm.
 * User: rlanders
 * Date: 7/13/15
 * Time: 12:55 PM
 */

namespace appti2ude;

use appti2ude\bones\MagicClass;

require_once './src/MagicClass.php';

class Inherit extends MagicClass {
	protected function InheritInitialize() {
		$this->AddProperty('test', 123);
	}
}

class MagicClassTest extends \PHPUnit_Framework_TestCase {
	function testInherits() {
		$test = new Inherit('test');
		$result = $test->Snapshot();
		$this->assertArrayHasKey('test', $result);
	}

	function testId() {
		$test = new Inherit('test');
		$result = $test->id;
		$this->assertEquals('test', $result);
	}

	function testName() {
		$test = new Inherit();
		$result = $test->Name();
		$this->assertEquals('Inherit', $result);
	}

	function testUniqueName() {
		$test = new Inherit();
		$result = $test->Name(true);
		$this->assertEquals('appti2ude\\Inherit', $result);
	}
}
