<?php
/**
 * Created by PhpStorm.
 * User: rlanders
 * Date: 7/8/15
 * Time: 10:28 PM
 */

namespace appti2ude;

/**
 * Class Event
 * @package appti2ude
 *
 * Handles event encapsulation
 *
 */
class Event {

	/**
	 * @var array The serialized data
	 */
	private $message = [];

	/**
	 * @param $id string id of the aggregate
	 * @param $data array serialized data to regenerate the event
	 */
	public function __constructor($id, $data) {
		$this->message = $data;
		$this->message['id'] = $id;
	}
}