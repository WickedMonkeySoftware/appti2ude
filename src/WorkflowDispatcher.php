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
        $this->AddProperty('watch', []);
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

    private function insert(&$haystack, &$needle, $position, $insertAfter) {
        ksort($haystack);
        $i = key($haystack);
        reset($haystack);
        foreach ($haystack as $key => $value) {
            if ($i == $position) {
                if ($insertAfter) {
                    $position += 1;
                }
                else {
                    $position -= 1;
                }
                $this->insert($haystack, $needle, $position, $insertAfter);
                return;
            }
            $i += 1;
            if (($i < $position && !$insertAfter) || ($i > $position && $insertAfter)) {
                break;
            }
        }

        $haystack[$position] = $needle;
    }

    public function Scan($instance) {
        $snap = Snapshot::TakeSnapshot($instance);
        $data = $snap->GetSnapshot();
        foreach ($data['iNeed'] as $event => $dependency) {
            foreach ($dependency as $order => $results) {
                $insertAfter = $order < 0 ? false : true;
                foreach($results as $requiredEvent => $callback) {
                    $callback = [$requiredEvent => [get_class($instance), $callback]];
                    $this->dependencies[$event] = $this->dependencies[$event] ?: [];
                    $this->insert($this->dependencies[$event], $callback, $order, $insertAfter);
                }
            }
        }
        $this->dispatch->Scan($instance);
    }

    public function GetStore() : IEventStore {
        return $this->dispatch->GetStore();
    }

    private function reverseArray($arr) {
        reset($arr);
        end($arr);
        for(end; key($arr) != null; prev($arr)) {
            yield current($arr);
        }
    }

    protected function doApply($event, $type) {
        if (isset($this->dependencies[$type])) {
            $watch = $this->dependencies[$type];
            foreach ($watch as $where => $do) {
                if ($where > 0) {
                    // protect the future
                    $this->watch[$event->id][] = [
                        $where => [$type => $do]
                    ];
                }
                else {
                    $eventsBack = -1;
                    $events = $this->dispatch->GetStore()->LoadEventsFor($event->id);
                    foreach ($this->reverseArray($events) as $oldEvent) {
                        if ($eventsBack == $where) {
                            foreach ($do as $requiredEvent => $callback) {
                                if ($oldEvent->type != $requiredEvent) {
                                    if ($callback[1] === true) {
                                        return MagicClass::CANCEL_ACTION;
                                    }
                                    else if (is_array($callback)) {
                                        foreach($callback as $cb => $data) {
                                            if ($cb == true) {
                                                return MagicClass::CANCEL_ACTION;
                                            }
                                            else {
                                                $this->dispatch->PublishEvent(new $cb($event->id, $data));
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        $eventsBack--;
                    }
                }
            }
        }
        if (isset($this->watch[$event->id])) {
            $watch = $this->watch[$event->id];
            foreach ($watch as $where => $do) {
                $eventsBack = 1;
                $events = $this->dispatch->GetStore()->LoadEventsFor($event->id);
                foreach ($this->reverseArray($events) as $oldEvent) {
                    if ($eventsBack == $where) {
                        foreach($do as $does) {
                            foreach ($does as $errorType => $wantedTypes) {
                                foreach ($wantedTypes as $wantedEvent => $callback) {
                                    if ($oldEvent->type == $errorType && $event->type != $wantedEvent) {
                                        if ($callback[1] === true) {
                                            return MagicClass::CANCEL_ACTION;
                                        }
                                        else if (is_array($callback)) {
                                            $origClass = $callback[0];
                                            $eventDef = $callback[1];
                                            if (is_array($eventDef)) {
                                                foreach ($eventDef as $evt => $data) {
                                                    if (is_numeric($evt) && $data === true) {
                                                        return MagicClass::CANCEL_ACTION;
                                                    }
                                                    if (class_exists($evt) && is_array($data) && self::$isProcessing == false) {
                                                        self::$isProcessing = true;
                                                        $this->dispatch->PublishEvent(new $evt($event->id, $data));
                                                        self::$isProcessing = false;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $eventsBack++;
                }
            }
        }
        /*if (isset($this->dependencies[$type])) {
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
        }*/
    }

    static $isProcessing = false;

    public function SendCommand(ICommand $command) {
        $this->dispatch->SendCommand($command);
    }

    public function PublishEvent(IEvent $event) {
        $this->dispatch->PublishEvent($event);
    }
}
