<?php
/**
 * Created by PhpStorm.
 * User: rlanders
 * Date: 7/11/15
 * Time: 12:56 AM
 */

namespace appti2ude\inter;

use appti2ude\Aggregate;
use appti2ude\Snapshot;

interface ISnapshot {
	static function Create(array $data, array $events) : Snapshot;
	static function Serialize(Aggregate $aggregate) : Snapshot;
	static function TakeSnapshot(Aggregate $aggregate) : Snapshot;
	function GetSnapshot() : array;
	function GetEvents() : array;
}