<?php
namespace appti2ude\inter;

use appti2ude\Aggregate;

interface IEventStore {
	public function LoadEventsFor($id) : array;
	public function SaveEventsFor($id, array $events);
}