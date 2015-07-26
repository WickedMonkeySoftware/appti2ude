<?

namespace appti2ude;

use appti2ude\bones\MagicClass;
use appti2ude\inter\ICommand;
use appti2ude\inter\IDispatch;
use appti2ude\inter\IEvent;

class WorkflowDispatcher extends MagicClass implements IDispatch {
    public function AddHandlerFor($command, $callback) {

    }

    public function AddSubscriberFor($event, $callback) {

    }

    public function Scan($instance) {

    }

    public function SendCommand(ICommand $command) {

    }

    public function PublishEvent(IEvent $event) {

    }
}
