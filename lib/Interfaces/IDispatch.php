<?php
namespace appti2ude\inter;

interface IDispatch {
	public function AddHandlerFor($command, $callback);
	public function AddSubscriberFor($event, $callback);
	public function Scan($instance);
	public function SendCommand(ICommand $command);
	public function PublishEvent(IEvent $event);
}