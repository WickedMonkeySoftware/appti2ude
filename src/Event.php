<?php
/**
 * Created by PhpStorm.
 * User: rlanders
 * Date: 7/8/15
 * Time: 10:28 PM
 */

namespace appti2ude;
use appti2ude\bones\MagicClass;
use appti2ude\inter\IEvent;

/**
 * Class Event
 * @package appti2ude
 *
 * Handles event encapsulation
 *
 */
class Event extends MagicClass implements IEvent {
	protected function EventInitialize() {
		$this->AddProperty('version', []);
	}
}
