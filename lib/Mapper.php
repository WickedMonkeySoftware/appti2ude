<?php

namespace appti2ude;

require_once 'magic_class.php';

use appti2ude\bones\MagicClass;

/**
 * Class Mapper
 * @package appti2ude
 */
class Mapper extends MagicClass {
	protected function MapperInitialize() {
		$this->AddProperty('events', []);
		$this->AddProperty('commands', []);
	}

	public function RegisterEvent($name, array $callback) {
		$this->events[$name][] = $callback;
	}

	public function RegisterCommand($name, array $callback) {
		$this->commands[$name][] = $callback;
	}
}

$GLOBALS['appti2ude_mapper'] = new Mapper();