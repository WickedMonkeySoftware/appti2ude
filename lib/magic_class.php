<?php

namespace appti2ude\bones;

/**
 * Class MagicClass
 * @package appti2ude\bones
 *
 * A class full of magical beings that can be easily serialized
 */
class MagicClass {

	/**
	 * @var array Allowed properties
	 *
	 *
	 * [
		'public' => [ // public properties go here
		'eventsLoaded' => ['public', 'private'], // set accessibility, read first, write second
		'id' => ['public', 'private'],
	;
	 */
	protected $properties = [];

	/**
	 * @var array serializable data
	 */
	protected $data = [];

	/**
	 * @param $obj mixed the object to dump
	 * @param bool|true $echo Echo or return?
	 */
	protected function debug($obj, $echo = true) {
		echo "<pre>";
		var_dump($obj);
		echo "</pre><br>";
	}

	public function snapshot() {
		return json_encode($this->data);
	}

	public static function hydrate($snapshot) {
		if (isset($snapshot->type) && class_exists($snapshot->type) && is_subclass_of($snapshot->type, __CLASS__)) {
			$agg = new $snapshot->type($snapshot->id, (array) $snapshot);
			return $agg;
		}

		throw new \Exception("Snapshot does not implement");
	}

	function __construct($id = null, $data = []) {
		$this->data = $data;
		$this->data['type'] = get_class($this);
		$this->data['id'] = $id;
	}

	/**
	 * @return null|string the calling class, or null if global
	 */
	private function getCallingClass() {
		$trace = debug_backtrace();
		$class = $trace[1]['class'];
		for ( $i=1; $i<count( $trace ); $i++ ) {
			if ( isset( $trace[$i] ) ) // is it set?
				if ( $class != $trace[$i]['class'] ) // is it a different class
					return $trace[$i]['class'];
		}

		return null;
	}

	/**
	 * @param $caller string the calling class to check
	 * @return bool
	 */
	private function isSameClass($caller) {
		if ($caller != get_class($this)) {
			return false;
		}

		return true;
	}

	/**
	 * @param $prop string The property to search for
	 * @param $goPrivate bool Are we doing a $this
	 * @param $getting bool Are we getting
	 * @param string $level string searching public or private
	 *
	 * @return bool
	 */
	private function findProperty($prop, $goPrivate, $getting, $level = 'public') {
		$getting = $getting ? 0 : 1;
		foreach ($this->properties[$level] as $property=>$access) {
			if ($prop == $property && (empty($access) || $access[$getting] == 'public' || ($goPrivate && $access[$getting] == 'private'))) {
				return true;
			}
		}

		if ($goPrivate) {
			$this->findProperty($prop, $goPrivate, $getting, 'private');
		}

		return false;
	}

	function __get($var) {
		$caller = $this->getCallingClass();

		if ($this->findProperty($var, $this->isSameClass($caller), true)) {
			return $this->data[$var];
		}
	}

	function __set($var, $value) {
		$caller = $this->getCallingClass();

		if ($this->findProperty($var, $this->isSameClass($caller), false)) {
			$this->data[$var] = $value;
		}
	}
}