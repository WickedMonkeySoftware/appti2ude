<?php

class test {
	var $var;

	function yielder($inc) {
		for ($i = 0; $i < 3; $i++) {
			yield $i;
		}
	}

	static function looper() {
		$har = ['test', 'yielder'];
		$yielder = $har[1];
		$test = $har[0];
		$arm = new $test();
		$x = new $test();

		foreach($arm->$yielder($x) as $count) {

		}
	}
}

/**
 * Created by PhpStorm.
 * User: rlanders
 * Date: 7/13/15
 * Time: 2:49 PM
 */
class MemoryEventStoreTest extends PHPUnit_Framework_TestCase {
	function testYield() {
		test::looper();
	}
}
