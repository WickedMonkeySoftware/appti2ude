<?php

namespace appti2ude\bones;
use appti2ude\inter\ISnapshot;

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
		'id' => ['public', 'private']
	   ]
	 */
	protected $properties = [
		'public' => [],
		'private' => []
	];

	/**
	 * @var array serializable data
	 */
	protected $data = [];

	/**
	 * @param $obj mixed the object to dump
	 * @param bool|true $echo Echo or return?
	 */
	protected function Debug($obj, $echo = true) {
		echo "<pre>";
		var_dump($obj);
		echo "</pre><br>";
	}

	public function Snapshot() {
		return $this->data;
	}

	public function Name($unique = false) {
		$name = get_class($this);

		if (!$unique) {
			$spread = explode('\\', $name);
			$name = end($spread);
		}

		return $name;
	}

	/**
	 * @param \stdClass $snapshot
	 *
	 * @return MagicClass
	 * @throws \Exception
	 */
	public static function Hydrate(\stdClass $snapshot) : MagicClass {
		if (isset($snapshot->type) && class_exists($snapshot->type) && is_subclass_of($snapshot->type, __CLASS__)) {
			$agg = new $snapshot->type($snapshot->id, (array) $snapshot);
			return $agg;
		}

		throw new \Exception("Snapshot does not implement");
	}

	function __construct($id = null, array $data = []) {
		//$this->data = [];
		$this->data['type'] = get_class($this);
		$this->data['id'] = $id;
		$this->Initialize($this->data['type']);
		$this->data = array_merge($this->data, $data);
		$this->data['type'] = get_class($this);
		$this->data['id'] = $id;
	}

	private function Initialize($class) {
		if (is_subclass_of($class, __CLASS__)) {
			$spread = explode('\\', $class);
			$classInit = end($spread);
			$initializer = $classInit . "Initialize";
			$this->Initialize(get_parent_class($class));
			echo "$initializer\n";
			if (method_exists($this, $initializer))
				$this->$initializer();
		}
	}

	protected function AddProperty($name, $default, $isPrivate = false, $readPrivate = false, $writePrivate = false) {
		$access = [];
		if (!$isPrivate) {
			$access = [
				$readPrivate ? 'private' : 'public',
				$writePrivate ? 'private' : 'public'
			];
		}
		$this->properties[$isPrivate ? 'private' : 'public'][$name] = $access;
		//$this->data[$name] = $default;
		$this->$name = $default;
	}

	/**
	 * @return null|string the calling class, or null if global
	 */
	private function GetCallingClass() {
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
	private function IsSameClass($caller) {
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
	private function FindProperty($prop, $goPrivate, $getting, $level = 'public') {
		$getting = $getting ? 0 : 1;
		foreach ($this->properties[$level] as $property=>$access) {
			if ($prop == $property)
				if ((empty($access) || $access[$getting] == 'public' || ($goPrivate && $access[$getting] == 'private'))) {
					return true;
			}
		}

		if ($goPrivate) {
			return $this->FindProperty($prop, $goPrivate, $getting, 'private');
		}

		return false;
	}

	function &__get($var) {
		$caller = $this->GetCallingClass();

		if ($var == 'type' || $var == 'id') {
			$return = &$this->data[$var];
		}

		if ($this->FindProperty($var, $this->IsSameClass($caller), true)) {
			$return = &$this->data[$var];
		}

		return $return;
	}

	function __set($var, $value) {
		$caller = $this->GetCallingClass();

		if ($this->FindProperty($var, $this->IsSameClass($caller), false)) {
			$this->data[$var] = $value;
		}
	}
}