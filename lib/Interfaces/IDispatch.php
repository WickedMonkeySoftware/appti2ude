<?php
namespace appti2ude\inter;

interface IDispatch {
	public function AddHandlerFor($command, array $callback);
	public function AddSubscriberFor($event, array $callback);
	public function Scan($instance);
	public function SendCommand(ICommand $command);
	public function PublishEvent(IEvent $event);
}