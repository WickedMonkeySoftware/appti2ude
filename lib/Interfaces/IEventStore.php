<?php
namespace appti2ude\inter;

use appti2ude\Aggregate;

interface IEventStore {
	public function LoadEventsFor($id, $aggregate) : array;
	public function SaveEventsFor($id, Aggregate $aggregate);
}