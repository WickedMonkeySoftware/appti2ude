<?php

require_once 'src/inter/ICommand.php';
require_once 'src/inter/IDispatch.php';
require_once 'src/inter/IEventStore.php';
require_once 'src/inter/IEvent.php';
require_once 'src/inter/ISnapshot.php';
require_once 'src/MagicClass.php';
require_once 'src/Aggregate.php';
require_once 'src/Event.php';
require_once 'src/Command.php';
require_once 'src/Snapshot.php';
require_once 'src/MemoryEventStore.php';
require_once 'src/MemoryDispatcher.php';
require_once 'src/WorkflowDispatcher.php';

$store = new \appti2ude\MemoryEventStore();
$dispatch = new appti2ude\MemoryDispatcher($store);
$workflow = new appti2ude\WorkflowDispatcher($dispatch);

class PressButton extends \appti2ude\Command {
    protected function PressButtonInitialize() {
        $this->AddProperty('button', 'clr');
    }
}

class NumberPushed extends \appti2ude\Event {
    protected function NumberPushedInitialize() {
        $this->AddProperty('value', 0);
    }
}

class ClrPushed extends \appti2ude\Event {
    protected function ClrPushedInitialize() {
        $this->AddProperty('pushed', true);
    }
}

class OperatorPushed extends \appti2ude\Event {
    protected function OperatorPushedInitialize() {
        $this->AddProperty('operator', '=');
    }
}

class PerformedLastOp extends \appti2ude\Event {
    protected function PerformedLastOpInitialize() {
        $this->AddProperty('operator', '');
    }
}

class CompletedCalc extends \appti2ude\Event {}

class ClrPushedError extends \appti2ude\Event {
    protected function ClrPushedErrorInitialize() {
        $this->AddProperty('Data', '');
        $this->AddProperty('Event', '');
    }
}

class Calculator extends \appti2ude\Aggregate {
    protected function CalculatorInitialize() {
        $this->AddProperty('value', '');
        $this->AddProperty('display', '');
        $this->AddProperty('register', '');
        $this->AddProperty('lastOp', '');
        $this->AddCommandHandler('PressButton', 'PressButton');
        $this->AddEventHandler('NumberPushed', 'NumberPushed');
        $this->AddEventHandler('CompletedCalc', 'CompletedCalc');
        $this->AddEventHandler('ClrPushed', 'ClrPushed');
        $this->AddEventHandler('OperatorPushed', 'OperatorPushed', [ // this should be [ -1: 'eventbefore', +1: 'eventafter' ]
            -1 => ['NumberPushed' => true], // require a number pushed, and fail silently if it isn't met
            1 => [
                'ClrPushed' => ['ClrPushedError' => ['Data' => 'Goes Here'], true],
            ]
        ]);
        $this->AddEventHandler('PerformedLastOp', 'PerformedLastOp');
        $this->AddEventHandler('ClrPushedError', 'Clr');
    }

    function PressButton($command) {
        if (preg_match('/^[0-9]$/', $command->button)) {
            yield new NumberPushed($this->id, ['value' => $command->button]);
        }

        else if (preg_match('/^(\\+|-|=)$/', $command->button)) {
            yield new OperatorPushed($this->id, ['operator' => $command->button]);
        }

        else if ($command->button == 'clr') {
            yield new ClrPushed($this->id);
        }
    }

    function Err($event) {
        $this->ApplyOneEvent(new ClrPushed($this->id));
        $this->ApplyOneEvent($event->Event);
    }

    function NumberPushed($event) {
        $number = $event->value;

        $this->register .= $number;
        $this->display .= $number;
    }

    function ClrPushed($event) {
        $this->register = '';
        $this->value = '';
        $this->display = "";
    }

    function CompletedCalc($event) {
        $this->display .= "=$this->value";
    }

    function PerformedLastOp($event) {
        $op = $event->operator;
        $number = $this->register;
        $this->doOp($op, $number, false);
        $this->ApplyOneEvent(new CompletedCalc($this->id));
    }

    private function doOp($op, $number, $doDisplay = true) {
        switch ($op) {
            case "=":
                $this->ApplyOneEvent(new PerformedLastOp($this->id, ['operator' => $this->lastOp]));
                return;
            case "+":
                $this->value += $number;
                break;
            case "-":
                $this->value -= $number;
                break;
            case "/":
                $this->value /= $number;
                break;
            case "*":
                $this->value *= $number;
                break;
        }
        $this->register = '';
        if ($doDisplay)
            $this->display .= $op;
        $this->lastOp = $op;
    }

    function OperatorPushed($event) {
        $op = $event->operator;
        $number = $this->register;
        //var_dump($op);
        $this->doOp($op, $number);
    }
}

$forscan = new Calculator();
$workflow->Scan($forscan);

class WorkflowDispatcherTest extends PHPUnit_Framework_TestCase {
    function testSimple() {
        $store = new appti2ude\MemoryEventStore();
        $dispatch = new appti2ude\MemoryDispatcher($store);
        $workflow = new appti2ude\WorkflowDispatcher($dispatch);
        $scan = new Calculator();
        $workflow->Scan($scan);

        $workflow->SendCommand(new PressButton('1', ['button' => 'clr']));
        $workflow->SendCommand(new PressButton('1', ['button' => '5']));
        $workflow->SendCommand(new PressButton('1', ['button' => '+']));
        $workflow->SendCommand(new PressButton('1', ['button' => '5']));
        $workflow->SendCommand(new PressButton('1', ['button' => '=']));

        $result = new Calculator(null, '1');
        $snapshot = \appti2ude\Snapshot::CreateFromStore($store, '1');
        $result->HydrateFromSnapshot($snapshot);

        $this->assertEquals('5+5=10', $result->display);
    }

    function testOutOfOrder() {
        //global $dispatch, $store;
        $store = new appti2ude\MemoryEventStore();
        $dispatch = new appti2ude\MemoryDispatcher($store);
        $workflow = new appti2ude\WorkflowDispatcher($dispatch);
        $scan = new Calculator();
        $workflow->Scan($scan);

        $workflow->SendCommand(new PressButton('1', ['button' => 'clr']));
        $workflow->SendCommand(new PressButton('1', ['button' => '5']));
        $workflow->SendCommand(new PressButton('1', ['button' => '+']));
        $workflow->SendCommand(new PressButton('1', ['button' => '+']));
        $workflow->SendCommand(new PressButton('1', ['button' => '5']));
        $workflow->SendCommand(new PressButton('1', ['button' => '5']));
        $workflow->SendCommand(new PressButton('1', ['button' => '=']));

        $result = new Calculator(null, '1');
        $snapshot = \appti2ude\Snapshot::CreateFromStore($store, '1');
        $result->HydrateFromSnapshot($snapshot);

        $this->assertEquals('5+55=60', $result->display);
    }

    function testErr() {
        $store = new appti2ude\MemoryEventStore();
        $dispatch = new appti2ude\MemoryDispatcher($store);
        $workflow = new appti2ude\WorkflowDispatcher($dispatch);
        $scan = new Calculator();
        $workflow->Scan($scan);

        $workflow->SendCommand(new PressButton('1', ['button' => 'clr']));
        $workflow->SendCommand(new PressButton('1', ['button' => '5']));
        $workflow->SendCommand(new PressButton('1', ['button' => '+']));
        $workflow->SendCommand(new PressButton('1', ['button' => '+']));
        $workflow->SendCommand(new PressButton('1', ['button' => '5']));
        $workflow->SendCommand(new PressButton('1', ['button' => '5']));
        $workflow->SendCommand(new PressButton('1', ['button' => '=']));
        $workflow->SendCommand(new PressButton('1', ['button' => '3']));

        $result = new Calculator(null, '1');
        $snapshot = \appti2ude\Snapshot::CreateFromStore($store, '1');
        $result->HydrateFromSnapshot($snapshot);

        $this->assertEquals('3', $result->display);
    }
}
