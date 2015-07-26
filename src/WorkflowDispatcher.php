<?

namespace appti2ude;

use appti2ude\bones\MagicClass;
use appti2ude\inter\ICommand;
use appti2ude\inter\IDispatch;
use appti2ude\inter\IEvent;

class WorkflowDispatcher extends MagicClass implements IDispatch {
    private $dispatch;

    protected function WorkflowDispatcherInitialize() {
        $this->AddProperty('dependencies', []);
    }

    function __construct(IDispatch $dispatcher, $id = null, $data = []) {
        parent::__construct($id, $data);
        $this->dispatch = $dispatcher;
    }

    public function AddHandlerFor($command, $callback) {
        $this->dispatch->AddHandlerFor($command, $callback);
    }

    public function AddSubscriberFor($event, $callback) {
        $this->dispatch->AddSubscriberFor($event, $callback);
    }

    public function Scan($instance) {
        $this->dispatch->Scan($instance);
    }

    public function SendCommand(ICommand $command) {
        $this->dispatch->SendCommand($command);
    }

    public function PublishEvent(IEvent $event) {
        $this->dispatch->PublishEvent($event);
    }
}
