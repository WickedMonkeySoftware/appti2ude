<?php

namespace appti2ude;

use appti2ude\bones\MagicClass;
use appti2ude\inter\ICommand;
use appti2ude\inter\IDispatch;
use appti2ude\inter\IEvent;
use appti2ude\inter\IEventStore;

class WorkflowDispatcher extends MagicClass implements IDispatch {
    private $dispatch;

    protected function WorkflowDispatcherInitialize() {
        $this->AddProperty('dependencies', []);
    }

    function __construct(IDispatch $dispatcher, $id = null, $data = []) {
        parent::__construct($id, $data);
        $this->dispatch = $dispatcher;
        $this->dispatch->AddAction('ApplyEvents', [$this, 'doApply'], 1);
    }

    public function AddHandlerFor($command, $callback) {
        $this->dispatch->AddHandlerFor($command, $callback);
    }

    public function AddSubscriberFor($event, $callback) {
        $this->dispatch->AddSubscriberFor($event, $callback);
    }

    public function Scan($instance) {
        $snap = Snapshot::TakeSnapshot($instance);
        $data = $snap->GetSnapshot();
        foreach ($data['iNeed'] as $event => $results) {
            foreach ($results as $requiredEvent => $callback) {
                $this->dependencies[$event][$requiredEvent][] = [get_class($instance), $callback];
            }
        }
        foreach ($data['iBlacklist'] as $event => $results) {
            foreach ($results as $blacklistedEvent => $callback) {
                $this->dependencies[$blacklistedEvent][$event][] = [get_class($instance), $callback];
            }
        }
        $this->dispatch->Scan($instance);
    }

    public function GetStore() : IEventStore {
        return $this->dispatch->GetStore();
    }

    protected function doApply($event, $type) {
        if (isset($this->dependencies[$type])) {
            // search for previous event
            $lastEvent = end($this->dispatch->GetStore()->LoadEventsFor($event->id))->type; // get the last event type
            foreach ($this->dependencies[$type] as $required => $callbacks) {
                echo "Sending $type: $required == $lastEvent\n";
                if (!empty($lastEvent) && $lastEvent != $required) {
                    foreach ($callbacks as $callback) {
                        if ($callback[1] === true) {
                            return MagicClass::CANCEL_ACTION;
                        }
                    }
                }
            }
        }
    }

    public function SendCommand(ICommand $command) {
        $this->dispatch->SendCommand($command);
    }

    public function PublishEvent(IEvent $event) {
        $this->dispatch->PublishEvent($event);
    }
}
